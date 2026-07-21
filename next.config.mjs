/** @type {import('next').NextConfig} */
// Static export so the whole site drops into Plesk's httpdocs with no Node
// runtime. Images are unoptimized because the export target has no image
// server; the only server-side piece remains public/contact.php.
const nextConfig = {
  output: 'export',
  trailingSlash: true,
  images: { unoptimized: true },
  reactStrictMode: true,
};

export default nextConfig;
