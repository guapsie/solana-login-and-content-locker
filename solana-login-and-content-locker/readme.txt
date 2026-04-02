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