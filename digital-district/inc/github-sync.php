<?php
/**
 * GitHub sync — import every repository from the owner's account into the
 * `project` post type, and keep them refreshed. Uses the public GitHub REST
 * API via wp_remote_get (no token required for public repos).
 *
 * Manual: wp-admin → Projects → "Sync GitHub". Automatic: daily cron.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

/**
 * GitHub username to import from. Filterable so it can be changed without
 * editing the theme.
 *
 * @return string
 */
function dd_github_user() {
	return (string) apply_filters( 'dd_github_user', 'cian-omalley' );
}

/**
 * Derive a project status from a repository's metadata.
 *
 * @param array $repo One repository from the API.
 * @return string One of the project statuses.
 */
function dd_github_status( $repo ) {
	$name = strtolower( (string) ( $repo['name'] ?? '' ) );
	$size = isset( $repo['size'] ) ? (int) $repo['size'] : 0;

	if ( ! empty( $repo['archived'] ) ) {
		return 'Complete';
	}
	// Template / learning repos are effectively finished artefacts.
	if ( preg_match( '/(template|starter|skills-)/', $name ) ) {
		return 'Complete';
	}
	// Near-empty repos are still being planned/scaffolded.
	if ( $size <= 12 ) {
		return 'Planning';
	}
	$pushed = isset( $repo['pushed_at'] ) ? strtotime( $repo['pushed_at'] ) : 0;
	if ( $pushed && $pushed > ( time() - 120 * DAY_IN_SECONDS ) ) {
		return 'In Progress';
	}
	return 'Planning';
}

/**
 * Fetch a repository README and convert it to HTML for the project body.
 * Server-side; returns '' if unavailable.
 *
 * @param string $user Owner.
 * @param string $repo Repo name.
 * @return string
 */
function dd_github_readme_html( $user, $repo ) {
	$url  = sprintf( 'https://api.github.com/repos/%s/%s/readme', rawurlencode( $user ), rawurlencode( $repo ) );
	$resp = wp_remote_get( $url, array(
		'timeout' => 15,
		'headers' => array(
			// The "raw" media type returns the README as markdown text.
			'Accept'     => 'application/vnd.github.raw+json',
			'User-Agent' => 'DigitalDistrict-Theme',
		),
	) );
	if ( is_wp_error( $resp ) || 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) {
		return '';
	}
	return dd_md_to_html( wp_remote_retrieve_body( $resp ) );
}

/**
 * Minimal, safe Markdown -> HTML converter for README bodies. Handles headings,
 * fenced/inline code, bold/italic, links, lists, blockquotes and rules. Badge
 * and inline images are dropped (they usually point off-site). Output is passed
 * through wp_kses_post, so anything unexpected is stripped.
 *
 * @param string $md Markdown source.
 * @return string Safe HTML.
 */
function dd_md_to_html( $md ) {
	$md    = str_replace( "\r\n", "\n", (string) $md );
	$lines = explode( "\n", $md );
	$html  = '';
	$in_code = false;
	$in_list = false;
	$para    = array();

	$flush_para = static function () use ( &$para, &$html ) {
		if ( $para ) {
			$html .= '<p>' . implode( ' ', $para ) . '</p>';
			$para  = array();
		}
	};
	$close_list = static function () use ( &$in_list, &$html ) {
		if ( $in_list ) {
			$html   .= '</ul>';
			$in_list = false;
		}
	};

	// Inline formatting applied to already-escaped text.
	$inline = static function ( $text ) {
		$text = esc_html( $text );
		$text = preg_replace( '/!\[[^\]]*\]\([^)]*\)/', '', $text ); // drop images/badges
		$text = preg_replace_callback( '/\[([^\]]+)\]\(([^)]+)\)/', static function ( $m ) {
			return '<a href="' . esc_url( html_entity_decode( $m[2] ) ) . '" rel="noopener">' . $m[1] . '</a>';
		}, $text );
		$text = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $text );
		$text = preg_replace( '/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text );
		$text = preg_replace( '/(?<!\*)\*(?!\*)([^*]+)\*/', '<em>$1</em>', $text );
		return $text;
	};

	foreach ( $lines as $line ) {
		if ( preg_match( '/^```/', $line ) ) {
			$flush_para();
			$close_list();
			if ( $in_code ) {
				$html   .= '</code></pre>';
				$in_code = false;
			} else {
				$html   .= '<pre><code>';
				$in_code = true;
			}
			continue;
		}
		if ( $in_code ) {
			$html .= esc_html( $line ) . "\n";
			continue;
		}

		$trim = trim( $line );

		if ( '' === $trim ) {
			$flush_para();
			$close_list();
			continue;
		}
		if ( preg_match( '/^(#{1,4})\s+(.*)$/', $trim, $m ) ) {
			$flush_para();
			$close_list();
			$level = min( 4, strlen( $m[1] ) + 1 ); // start at h2 inside content
			$html .= '<h' . $level . '>' . $inline( $m[2] ) . '</h' . $level . '>';
			continue;
		}
		if ( preg_match( '/^(-{3,}|\*{3,})$/', $trim ) ) {
			$flush_para();
			$close_list();
			$html .= '<hr />';
			continue;
		}
		if ( preg_match( '/^[-*]\s+(.*)$/', $trim, $m ) ) {
			$flush_para();
			if ( ! $in_list ) {
				$html   .= '<ul>';
				$in_list = true;
			}
			$html .= '<li>' . $inline( $m[1] ) . '</li>';
			continue;
		}
		if ( preg_match( '/^>\s?(.*)$/', $trim, $m ) ) {
			$flush_para();
			$close_list();
			$html .= '<blockquote><p>' . $inline( $m[1] ) . '</p></blockquote>';
			continue;
		}

		$para[] = $inline( $trim );
	}
	$flush_para();
	$close_list();
	if ( $in_code ) {
		$html .= '</code></pre>';
	}

	return wp_kses_post( $html );
}

/**
 * Turn a repo slug into a readable title, e.g. "ai-os" -> "Ai Os".
 *
 * @param string $name Repo name.
 * @return string
 */
function dd_prettify_repo( $name ) {
	$name = str_replace( array( '-', '_' ), ' ', $name );
	return ucwords( trim( $name ) );
}

/**
 * Fetch and upsert all public repositories.
 *
 * @return int|WP_Error Number of repositories synced, or an error.
 */
function dd_sync_github() {
	$user = rawurlencode( dd_github_user() );
	$url  = "https://api.github.com/users/{$user}/repos?per_page=100&sort=updated&type=owner";

	$response = wp_remote_get( $url, array(
		'timeout' => 20,
		'headers' => array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'DigitalDistrict-Theme',
		),
	) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}
	if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'dd_github_http', __( 'GitHub API request failed.', 'digital-district' ) );
	}

	$repos = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $repos ) ) {
		return new WP_Error( 'dd_github_json', __( 'Could not read the GitHub response.', 'digital-district' ) );
	}

	$count = 0;
	foreach ( $repos as $i => $repo ) {
		if ( empty( $repo['id'] ) ) {
			continue;
		}
		$repo_id = (int) $repo['id'];

		// Find an existing project for this repo.
		$existing = get_posts( array(
			'post_type'   => 'project',
			'post_status' => 'any',
			'meta_key'    => 'dd_repo_id',
			'meta_value'  => $repo_id,
			'fields'      => 'ids',
			'numberposts' => 1,
		) );
		$post_id = $existing ? (int) $existing[0] : 0;

		$desc = isset( $repo['description'] ) ? (string) $repo['description'] : '';

		$data = array(
			'post_type'    => 'project',
			'post_status'  => 'publish',
			'post_excerpt' => $desc,
		);

		if ( $post_id ) {
			// Preserve manual title/content edits; refresh excerpt only.
			$data['ID'] = $post_id;
			$post_id    = wp_update_post( $data, true );
		} else {
			// Pull the repo README for a detailed body; fall back to the description.
			$body                 = dd_github_readme_html( dd_github_user(), $repo['name'] );
			$data['post_title']   = dd_prettify_repo( $repo['name'] );
			$data['post_content'] = $body ? $body : ( $desc ? '<p>' . esc_html( $desc ) . '</p>' : '' );
			$data['menu_order']   = 100 + $i; // GitHub imports sit after curated ones.
			$post_id              = wp_insert_post( $data, true );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, 'dd_repo_id', $repo_id );
		update_post_meta( $post_id, 'dd_repo_url', esc_url_raw( $repo['html_url'] ?? '' ) );
		if ( ! empty( $repo['homepage'] ) ) {
			update_post_meta( $post_id, 'dd_live_url', esc_url_raw( $repo['homepage'] ) );
		}
		if ( ! empty( $repo['created_at'] ) ) {
			update_post_meta( $post_id, 'dd_year', gmdate( 'Y', strtotime( $repo['created_at'] ) ) );
		}

		// Set status only when it is not already set, so manual edits persist.
		if ( '' === (string) get_post_meta( $post_id, 'dd_status', true ) ) {
			update_post_meta( $post_id, 'dd_status', dd_github_status( $repo ) );
		}

		// Technologies: language + topics.
		$terms = array();
		if ( ! empty( $repo['language'] ) ) {
			$terms[] = $repo['language'];
		}
		if ( ! empty( $repo['topics'] ) && is_array( $repo['topics'] ) ) {
			$terms = array_merge( $terms, array_slice( $repo['topics'], 0, 5 ) );
		}
		if ( $terms ) {
			wp_set_object_terms( $post_id, $terms, 'tech', false );
		}

		++$count;
	}

	update_option( 'dd_github_last_sync', time() );
	return $count;
}

/**
 * Admin button under Projects → Sync GitHub.
 */
function dd_github_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=project',
		__( 'Sync GitHub', 'digital-district' ),
		__( 'Sync GitHub', 'digital-district' ),
		'edit_posts',
		'dd-sync-github',
		'dd_github_admin_page'
	);
}
add_action( 'admin_menu', 'dd_github_admin_menu' );

function dd_github_admin_page() {
	$last = (int) get_option( 'dd_github_last_sync' );
	echo '<div class="wrap"><h1>' . esc_html__( 'Sync projects from GitHub', 'digital-district' ) . '</h1>';
	echo '<p>' . sprintf(
		/* translators: %s: GitHub username. */
		esc_html__( 'Imports every public repository from %s into Projects. Titles and write-ups you edit by hand are preserved on re-sync.', 'digital-district' ),
		'<code>' . esc_html( dd_github_user() ) . '</code>'
	) . '</p>';
	if ( $last ) {
		echo '<p><em>' . esc_html( sprintf( __( 'Last synced: %s ago.', 'digital-district' ), human_time_diff( $last ) ) ) . '</em></p>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="dd_sync_github" />';
	wp_nonce_field( 'dd_sync_github', 'dd_sync_github_nonce' );
	submit_button( __( 'Sync now', 'digital-district' ) );
	echo '</form></div>';
}

/**
 * Handle the manual sync button.
 */
function dd_github_handle_sync() {
	if ( ! current_user_can( 'edit_posts' ) || ! isset( $_POST['dd_sync_github_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dd_sync_github_nonce'] ), 'dd_sync_github' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'digital-district' ) );
	}
	$result = dd_sync_github();
	$args   = is_wp_error( $result )
		? array( 'dd_sync' => 'err' )
		: array( 'dd_sync' => 'ok', 'dd_n' => (int) $result );
	wp_safe_redirect( add_query_arg( $args, admin_url( 'edit.php?post_type=project&page=dd-sync-github' ) ) );
	exit;
}
add_action( 'admin_post_dd_sync_github', 'dd_github_handle_sync' );

function dd_github_admin_notice() {
	if ( empty( $_GET['dd_sync'] ) ) {
		return;
	}
	$state = sanitize_key( wp_unslash( $_GET['dd_sync'] ) );
	if ( 'ok' === $state ) {
		$n = isset( $_GET['dd_n'] ) ? (int) $_GET['dd_n'] : 0;
		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( sprintf( __( 'Synced %d repositories from GitHub.', 'digital-district' ), $n ) ) );
	} else {
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html__( 'GitHub sync failed — check the server can reach api.github.com.', 'digital-district' ) );
	}
}
add_action( 'admin_notices', 'dd_github_admin_notice' );

/**
 * Daily refresh via cron.
 */
add_action( 'dd_github_cron', 'dd_sync_github' );
