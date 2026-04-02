=== Solana Login and Content Locker ===
Contributors: guapsie
Tags: solana, web3, phantom, authentication, login, content locker
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Web3 authentication gateway using Solana wallets. Say goodbye to passwords.

== Description ==
Solana Login and Content Locker is a Web3 authentication plugin for WordPress that allows you to token-gate your content or create exclusive areas using the Phantom wallet on the Solana blockchain.

Say goodbye to traditional email/password logins and embrace the decentralized web.

= Key Features =

Phantom Wallet Support: Seamlessly connect and authenticate users via Phantom.

Cryptographic Security: Utilizes robust Ed25519 signature verification via libsodium to ensure maximum security against spoofing.

Content Protection: Easily lock your posts and pages. Only authenticated Web3 users can access the content.

Automatic Account Creation: Automatically handles WordPress sessions and creates standard subscriber accounts for authenticated wallets.

Lightweight & Fast: Strictly object-oriented architecture that won't slow down your site. Loads official Solana libraries securely from an external CDN.

== Installation ==

Log into your WordPress admin dashboard.

Navigate to Plugins > Add New.

Search for "Solana Login and Content Locker" and click "Install Now", or upload the solana-login-and-content-locker.zip file.

Click "Activate Plugin".

Navigate to the new Solana Web3 menu in your sidebar to configure the access rules.

Use the [slcl_login] shortcode on any page, post, or widget to display the connect button.

== Third-Party or External Services ==

To provide secure and seamless Web3 authentication without bloating your server, this plugin relies on the following external services:

= 1. UNPKG CDN (unpkg.com) =
* **What it is used for:** The plugin enqueues the official `@solana/web3.js` library directly from the UNPKG Content Delivery Network. This ensures the library is delivered securely, transparently, and incredibly fast, complying with WordPress directory guidelines against bundling minified, unreadable code.
* **What data is sent and when:** When a visitor loads a page containing the login button or protected content, their browser sends a standard HTTP request to UNPKG to download the JavaScript file. Standard connection data (such as the user's IP address and browser user agent) is visible to the CDN during this request. No personal data from your WordPress site is sent to them.
* **Terms & Privacy:** UNPKG is an open-source public CDN sponsored by Cloudflare. 
[Cloudflare Privacy Policy](https://www.cloudflare.com/privacypolicy/)

= 2. Phantom Wallet (Phantom.app) =
* **What it is used for:** The plugin interacts with the user's Phantom browser extension to securely authenticate them via the Solana blockchain.
* **What data is sent and when:** When a user clicks "Connect Phantom", the plugin simply requests the user's Solana Public Key (wallet address). No private keys, balances, or personal identifiable information (PII) are ever accessed or transmitted. The public key and a cryptographic signature are then sent securely to your own WordPress server for validation.
* **Terms & Privacy:** [Phantom Privacy Policy](https://phantom.app/privacy) | [Phantom Terms of Service](https://phantom.app/terms)

== Frequently Asked Questions ==

= Do I need an SSL certificate? =
Yes. Web3 wallet extensions require a secure context (HTTPS) to interact with your website. The connection will fail on standard HTTP connections.

= What happens if a user doesn't have a Phantom wallet installed? =
The plugin intelligently detects the missing browser extension and provides a direct, safe link for the user to download the Phantom wallet.

== Changelog ==

= 1.0.0 =

Initial release.

Added [slcl_login] shortcode functionality.

Integrated Phantom wallet UI.

Implemented secure Ed25519 cryptographic signature verification.

Added backend settings panel.