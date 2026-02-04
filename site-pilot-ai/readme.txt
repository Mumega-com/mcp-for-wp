=== Site Pilot AI ===
Contributors: digidinc
Donate link: https://sitepilotai.com
Tags: ai, claude, mcp, wordpress, elementor
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Control WordPress with AI. Expose posts, pages, media, and Elementor to AI assistants via the Model Context Protocol (MCP).

== Description ==

Site Pilot AI lets you control your WordPress site using AI assistants like Claude. Using the Model Context Protocol (MCP), your AI assistant can create posts, manage pages, upload media, and work with Elementor - all through natural language.

= Key Features =

* **Content Management** - Create, edit, and delete posts and pages
* **Media Handling** - Upload files or import from URLs
* **Draft Management** - List and bulk-delete drafts
* **Basic Elementor** - Get and set Elementor page data
* **Secure API** - API key authentication with activity logging
* **MCP Compatible** - Works with Claude Code and Claude Desktop

= How It Works =

1. Install and activate the plugin
2. Copy your API key from Tools → Site Pilot AI
3. Configure your MCP server with the API key
4. Start controlling WordPress with natural language

= Example Commands =

* "Create a blog post about summer recipes"
* "List all draft pages"
* "Upload this image and set it as the featured image for post 123"
* "Get the Elementor data for the homepage"

= Pro Features =

Upgrade to Site Pilot AI Pro for advanced features:

* **Full Elementor Integration** - Templates, landing pages, clone pages, widget control
* **SEO Tools** - Yoast, RankMath, AIOSEO, SEOPress integration
* **Forms** - Contact Form 7, WPForms, Gravity Forms, Ninja Forms
* **Landing Page Builder** - One-click AI-generated landing pages
* **Priority Support** - 48-hour response time

[Learn more about Pro →](https://sitepilotai.com/pricing)

== Installation ==

= From WordPress Admin =

1. Go to Plugins → Add New
2. Search for "Site Pilot AI"
3. Click Install Now, then Activate
4. Go to Tools → Site Pilot AI to get your API key

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Select the ZIP file and click Install Now
4. Activate the plugin
5. Go to Tools → Site Pilot AI to get your API key

= MCP Server Setup =

Add to your `~/.claude.json`:

`{
  "mcpServers": {
    "site-pilot-ai": {
      "command": "node",
      "args": ["/path/to/mcp-server/dist/index.js"],
      "env": {
        "WP_URL": "https://yoursite.com",
        "WP_API_KEY": "spai_your_api_key_here"
      }
    }
  }
}`

== Frequently Asked Questions ==

= What is MCP? =

Model Context Protocol (MCP) is an open protocol that enables AI assistants like Claude to interact with external tools and services. Site Pilot AI exposes your WordPress site as an MCP-compatible tool.

= Is this secure? =

Yes. All requests require a unique API key. Keys are securely stored in your WordPress database. Activity logging tracks all API usage for auditing.

= Does it work with any AI? =

Site Pilot AI works with any AI assistant that supports the MCP protocol. Currently, this includes Claude Code and Claude Desktop. More integrations are planned.

= Do I need coding skills? =

No. Once configured, you control WordPress through natural language. The AI handles all the technical details.

= What about Elementor? =

The free version includes basic Elementor support (get/set page data). The Pro version adds templates, landing pages, widgets, and full page building capabilities.

= Can I use this on multiple sites? =

Each site needs its own plugin installation and API key. The Pro version includes multi-site management features.

== Screenshots ==

1. Admin dashboard with API key management
2. Detected plugins and capabilities
3. Available API endpoints
4. Creating a post with Claude
5. Elementor integration in action

== Changelog ==

= 1.0.0 =
* Initial release
* Posts and pages CRUD operations
* Media upload (file and URL)
* Draft management
* Basic Elementor support
* API key authentication
* Activity logging
* Admin settings page

== Upgrade Notice ==

= 1.0.0 =
Initial release of Site Pilot AI. Control WordPress with AI assistants!

== Privacy Policy ==

Site Pilot AI does not collect or transmit any data to external servers. All data stays on your WordPress installation. Activity logs are stored locally and can be configured or disabled in settings.

== Support ==

* Documentation: [sitepilotai.com/docs](https://sitepilotai.com/docs)
* Support Forum: [wordpress.org/support/plugin/site-pilot-ai](https://wordpress.org/support/plugin/site-pilot-ai)
* GitHub: [github.com/Digidinc/site-pilot-ai](https://github.com/Digidinc/site-pilot-ai)
