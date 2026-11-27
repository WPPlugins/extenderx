=== ExtenderX – Accessibility Toolbar, Dark Mode & Site Tools ===
Version: 1.11.2
Author: WPXERO
Author URI: https://wpxero.com
Donate link: https://wpxero.com/plugins/extenderx/pricing
Contributors: wpxero
Tags: accessibility, accessibility toolbar, accessibility widget, a11y, wcag
Requires at least: 6.2
Tested up to: 7.1
Stable tag: 1.11.2
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free accessibility toolbar: text size, contrast, dyslexia & low-vision profiles. No cookies, no external service, no subscription.

== Description ==

**Some of your visitors can't read your site comfortably.** Text is too small, contrast is too low, the font is hard going with dyslexia, or the motion makes them feel unwell. Most "accessibility widgets" fix this by loading a script from someone else's server, charging a monthly fee, and promising legal compliance they can't deliver.

**ExtenderX takes a different route.** It adds a free accessibility toolbar to any WordPress theme. Visitors open it with one click or **Alt+A** and adjust the page to suit them: bigger text, higher contrast, a readable font, paused animations, or a one-click profile for low vision, dyslexia, focus or seizure safety.

* **Free.** The toolbar, 11 visitor tools, 4 quick profiles, a skip link and focus outlines cost nothing.
* **Private.** No cookies, no tracking, nothing fetched from an external service. A visitor's choices stay in their own browser.
* **Honest.** It does not claim to make your site "compliant", because no plugin can. It gives visitors control and gives you tools to find problems in your own content.
* **Light.** Nothing loads on your site until you switch it on.

[Live demo](https://wpxero.com/plugins/extenderx?utm_source=wporg&utm_medium=readme&utm_campaign=demo) | [Compare Free vs Pro](https://wpxero.com/plugins/extenderx/pricing?utm_source=wporg&utm_medium=readme&utm_campaign=top)

### Accessibility toolbar (free)

Tools that let visitors adjust how your site is displayed, so they can read and navigate it more comfortably. Everything is off until you enable it, nothing is fetched from an external service, and no visitor data leaves their browser.

- **Accessibility Toolbar** — a floating button that opens a panel of display controls, grouped into Text, Color, Content, and Motion & reading. Free tools: text size, a readable font, left-aligned text, high contrast, inverted colors, grayscale, a light background, underlined links, highlighted headings, hidden images, and paused motion.
- **Quick profiles** — **Low vision**, **Dyslexia**, **Focus** and **Seizure safe** each switch on a matching set of tools in one click, using only the tools you have enabled. Press again to clear.
- **Keyboard shortcut** — visitors open and close the panel with **Alt+A** from anywhere on the page. A badge on the button shows how many settings are active, and **Reset all** clears them.
- **Remembered preferences** — a visitor's choices follow them from page to page, stored in their own browser. No cookies, nothing identifying recorded, and nothing sent to your server.
- **Skip to Content Link** — lets keyboard visitors jump past the header straight to your main content. Hidden until focused, and it moves focus properly rather than only scrolling. If your target selector doesn't match, common landmarks are tried automatically.
- **Enhanced Focus Outlines** — a configurable ring (color, thickness, offset) around whichever element the keyboard is on, for themes that hide or wash out the browser default. Appears for keyboard input, not mouse clicks.

You choose which side the button sits on, how far up from the bottom, which tools appear, and whether the profiles row and the shortcut are offered.

The toolbar is itself keyboard operable, announces changes to screen readers, and honors the visitor's "prefers-reduced-motion" system setting — motion starts paused for those visitors, and turning that off will not force animations back on for them.

### Not an overlay, and no compliance promises

ExtenderX does not rewrite your markup, inject fixes from a remote service, or claim to make your site meet WCAG, the ADA or the European Accessibility Act. Those depend on your content, your theme and your own testing with real assistive technology. The toolbar is one part of that work: it lets visitors change how your site is displayed, and the Pro audit tools help you find gaps in your own content.

### Pro accessibility tools

- **Seven more visitor tools** — reading guide, reading mask, text spacing, content zoom, the OpenDyslexic typeface (bundled with the plugin and served from your own site, downloaded only when a visitor turns it on), a large cursor, and saturation control.
- **Toolbar branding** — button and icon colors, four icon choices, button size, stacking order, and hide-on-mobile.
- **Accessibility Statement** — generates an editable *draft* page describing the steps you take, what the site offers, what is still unfinished, and how visitors can report a problem. Written as prompts for you to complete; it asserts nothing about meeting any particular standard or law on your behalf.
- **Alt Text Audit** — lists images in your media library with no alternative text, paginated for large libraries, with a link to fix each one.

---

### Also included: site tools for any theme

ExtenderX also ships a set of site tools you can switch on from the same dashboard, so you don't need a separate plugin for each. They work on any WordPress theme — classic or block, with or without the Gutenberg editor. A disabled tool adds no CSS or JavaScript to your pages.

### Block Editor Extensions (all free)

Six extensions added to the Gutenberg sidebar for every registered block — core WordPress blocks and third-party block libraries alike. No replacement blocks. No shortcodes.

* **Entrance Animation** — scroll-triggered effects (fade in, slide up, zoom, flip, bounce, and more) with duration, delay, and easing controls. Fires once when the block enters the viewport.
* **Floating Animation** — smooth, continuous looping motion for hero sections, testimonial cards, and promotional banners.
* **Tilt Animation** — GPU-accelerated 3D tilt that responds to cursor movement, powered by VanillaTilt.js.
* **Visibility Control** — show or hide any block on desktop, tablet, or mobile using clean CSS.
* **Wrapper Link** — make any block — group, column, image, card — fully clickable.
* **Tooltip** — accessible hover tooltips on any block, rendered by Tippy.js.

Scripts and styles are added only to pages where at least one block uses an extension.

**Compatible block libraries:** Kadence Blocks · Spectra (UAG) · Stackable · Essential Blocks · Ultimate Blocks · Ghostkit · Gutenverse · GutenKit · Otter Blocks · Greenshift · CoBlocks · GenerateBlocks · all core WordPress blocks

### Motion & Animations (Beta)

* **Site-Wide Animations** (free) — set one reveal style and it applies automatically to your content across every page. Supports block-type targeting, stagger delays, and per-page or per-block overrides in the editor.
* **Scroll-Linked Effects** (Pro) — parallax drift and scroll-progress scrub for elements you target.
* **Import / Export** (Pro) — back up your motion configuration or move it between sites.

### Appearance

* **Dark Mode** — visitor-facing dark/light toggle powered by DarkReader. Works with any theme, no configuration.
* **Skeleton Loading Screen** (Pro) — show a skeleton placeholder while page content loads.
* **Preloader** (Pro) — display a branded animation before the page reveals.

### Navigation & Scrolling

* **Back to Top Button** — floating button with style presets, icon, position, and visibility options.
* **Sticky Header** — keeps your navigation fixed at the top of the viewport as visitors scroll.
* **Sticky Footer Bar** — persistent footer strip across all pages.
* **Scroll Progress Bar** — visual reading-progress indicator at the top of the page.
* **Scrollspy** — highlights the active navigation link based on scroll position.
* **Smart Scroll** — hides the header when scrolling down, reveals it when scrolling up.
* **Scroll to Bottom Button** (Pro) — one-click jump to the bottom of the page; stacks neatly beside Back to Top.
* **Smooth Scrolling** (Pro) — smooth page scrolling powered by Lenis.
* **Scrollbar Styler** (Pro) — customize the browser scrollbar color and appearance.

### Site Access Modes

* **Maintenance Mode** — show visitors a maintenance page while you work; admins keep full site access.
* **Coming Soon Mode** — full-page coming-soon screen before launch.
* **Age Verification** (Pro) · **Cookie Consent** (Pro) · **GDPR Notice** (Pro) · **Exit-Intent Popup** (Pro)

### Marketing

* **Announcement Bar** — dismissible top or bottom banner for offers and notices.
* **Floating CTA Button** — fixed call-to-action button that follows the visitor.
* **CTA Pulse Animation** — draw attention to any button with a pulse ring.
* **Sticky WhatsApp Button** (Pro) — floating WhatsApp chat widget.
* **Global Countdown Banner** (Pro) — site-wide urgency timer for sales and launches.

### Custom Code

* **Insert Headers & Footers** — add HTML, CSS, or JavaScript to your site's `<head>`, after the opening `<body>`, or before the closing `</body>`. Suited to Google Analytics, Google Tag Manager, Meta Pixel, AdSense, and site verification tags.
* **Code Snippet Library** — create, name, and toggle reusable HTML, CSS, and JavaScript snippets. Disabled snippets stay saved but stop running.
* **Pro adds** PHP snippets, conditional display logic (specific pages or post types), and scheduling.

### Save & Go

A menu in the post editor (block and classic) that saves and then takes you straight to the next step.

* **Free:** Save & New, Save & List, Save & Next, Save & Previous, Save & View.
* **Pro:** Save & Duplicate, Save & Return, Save & View in New Window, a choice of default action, button placement options, and **Bulk Add** — paste a list of titles and create posts, pages or custom post types in one go.

### Seasonal Effects

* **Free:** Winter Snow, Autumn Leaves, Falling Hearts.
* **Pro:** Spring Petals, Summer Bubbles, Christmas Lights, Fireworks, Halloween Bats, Ramadan Lanterns, Black Friday and Cyber Monday confetti, Weather, Particle Background, Fireflies, Twinkling Stars, Floating Emojis, Floating Shapes, Light Beams, Mouse Trail — plus scheduling by date range, page targeting, and per-effect colors.

All effects honor the visitor's "prefers-reduced-motion" system setting.

### Content Protection (all free)

Disable Right Click · Disable Copy · Disable Inspect · Disable Image Drag · Hide Admin Bar on Frontend

---

### Free vs Pro at a glance

**Free — yours to keep:**
All 6 Block Editor extensions · Accessibility Toolbar with 11 visitor tools, 4 quick profiles, Alt+A shortcut and remembered preferences · Skip to Content link · Enhanced Focus Outlines · Site-Wide Animations (beta) · Insert Headers & Footers + HTML/CSS/JS snippets · Dark Mode · Back to Top · Sticky Header · Sticky Footer Bar · Scroll Progress Bar · Smart Scroll · Scrollspy · Maintenance Mode · Coming Soon Mode · Announcement Bar · Floating CTA Button · CTA Pulse Animation · 5 Save & Go actions · Winter Snow · Autumn Leaves · Falling Hearts · All 5 Content Protection tools · Full RTL support

**Pro — when you need more:**
7 more accessibility tools · Accessibility toolbar branding · Accessibility Statement generator · Alt Text Audit · Scroll-Linked Effects (parallax, scrub) · Motion Import/Export · Scroll to Bottom button · Smooth Scrolling (Lenis) · Scrollbar Styler · Skeleton Loading Screen · Preloader · 16 more seasonal and ambient effects · Seasonal scheduling, targeting and colors · Sticky WhatsApp Button · Global Countdown Banner · Advanced marketing widget options · Age Verification · Cookie Consent · GDPR Notice · Exit-Intent Popup · PHP snippets · Conditional snippet display · Snippet scheduling · 3 more Save & Go actions · Bulk Add

### Credits

The Dyslexia font tool uses [OpenDyslexic](https://opendyslexic.org/) by Abbie Gonzalez, licensed under the SIL Open Font License 1.1 (included in `assets/fonts/opendyslexic/OFL.txt`).

### Is ExtenderX Pro right for you?

Start with the free plugin. Upgrade when one of these matches what you are doing:

* **You run a store or run promotions** — countdown banner, exit-intent popup, WhatsApp button, and Black Friday / Cyber Monday effects you can schedule ahead of time.
* **You need consent and age gates** — Cookie Consent, GDPR Notice and Age Verification without adding three more plugins.
* **You are working on accessibility** — an Alt Text Audit to find images to fix, an Accessibility Statement draft, extra reading tools, and a toolbar that matches your brand.
* **You build sites for clients** — PHP snippets with conditional logic, Motion Import/Export between sites, and Bulk Add to scaffold pages in seconds.
* **You care about polish** — smooth scrolling, parallax, preloader, skeleton loading screen and a styled scrollbar.

Upgrading keeps every setting you already have. Pro installs alongside the free plugin and unlocks the features marked **PRO** in the same dashboard — nothing to migrate or reconfigure.

[See plans and pricing →](https://wpxero.com/plugins/extenderx/pricing?utm_source=wporg&utm_medium=readme&utm_campaign=upgrade)

== Installation ==

1. In your WordPress admin, go to **Plugins → Add New**, search for **ExtenderX**, then click **Install Now** and **Activate**. Or upload the `extenderx` folder to `/wp-content/plugins/` manually.
2. After activation, open **ExtenderX** in the left admin menu.
3. To add the accessibility toolbar, go to **Site Enhancements → Accessibility** and enable **Accessibility Toolbar**, then visit your site and press **Alt+A**.
4. Use the **Quick Start** screen to enable any other modules you want: Site Enhancements, Block Editor extensions, and/or Custom Code.
5. Configure each module from the sidebar: **Site Enhancements** for site-wide tools (including the **Accessibility** tab), **Block Editor** for Gutenberg block extensions, **Custom Code** for header/footer scripts and snippets.
6. For block animations: edit any page or post, select a block, and open the **ExtenderX** panel in the right sidebar.

**To add Pro:** purchase a plan at [wpxero.com](https://wpxero.com/plugins/extenderx/pricing?utm_source=wporg&utm_medium=readme&utm_campaign=install), install the ExtenderX Pro plugin alongside this one, and activate your licence. Keep the free plugin active — Pro extends it.

== Frequently Asked Questions ==

= What accessibility tools does ExtenderX include, and are they free? =

The core tools are free: an **Accessibility Toolbar** with 11 visitor display controls (text size, readable font, left-aligned text, high contrast, inverted colors, grayscale, light background, underlined links, highlighted headings, hidden images, paused motion), four **quick profiles** (Low vision, Dyslexia, Focus, Seizure safe), an **Alt+A** keyboard shortcut, remembered preferences, a **Skip to Content** link, and **Enhanced Focus Outlines**. Pro adds seven more visitor tools, toolbar branding, an Accessibility Statement generator, and an Alt Text Audit.

= Does this plugin make my site WCAG, ADA, or EAA compliant? =

No — and be careful with any plugin that claims otherwise. ExtenderX gives visitors control over how your site is displayed and gives you two tools for finding problems in your own content. Compliance depends on your content, your markup, your theme, and your own testing with real assistive technology. Treat these tools as one part of the work, not a substitute for it.

= Is ExtenderX an accessibility overlay? =

Not in the usual sense. Overlay services load a script from their own servers, try to rewrite your page automatically, and often promise compliance. ExtenderX runs entirely on your own site, sends nothing anywhere, and only changes how the page is displayed when a visitor asks it to. It doesn't rewrite your content or claim to fix it.

= Does ExtenderX help with the European Accessibility Act (EAA)? =

It helps, but it is not compliance on its own. The EAA has applied to many businesses selling products and services to people in the EU, including online shops, since 28 June 2025. Meeting it depends on your content, markup and theme. ExtenderX gives visitors display controls, adds a skip link and visible focus outlines, and Pro helps you draft an accessibility statement and find images missing alt text. Use those alongside your own testing, not instead of it.

= How do I add an accessibility widget to my WordPress site? =

Go to **ExtenderX → Site Enhancements → Accessibility** and enable **Accessibility Toolbar**. Use the gear icon to pick which side the button sits on, how far up from the bottom, which tools appear, and whether the quick profiles row and the Alt+A shortcut are offered. Nothing loads on your site until you enable it.

= Does the accessibility toolbar set cookies or send visitor data anywhere? =

No. A visitor's choices are stored in their own browser so they carry from page to page. No cookies are set, nothing identifying is recorded, nothing is sent to your server, and nothing is fetched from an external service. You can switch remembering off in the toolbar settings, though visitors then have to set their preferences again on every page.

= How do I add a skip to content link? =

Enable **Skip to Content Link** under **ExtenderX → Site Enhancements → Accessibility** and set **Target** to a CSS selector for your main content area — `#main` suits most themes. The link stays invisible until a keyboard visitor tabs to it, then moves focus into the content rather than only scrolling. If the selector matches nothing, common landmarks are tried automatically, so it keeps working if you change themes.

= My theme hides the keyboard focus ring. Can ExtenderX fix that? =

Yes. Enable **Enhanced Focus Outlines** under **ExtenderX → Site Enhancements → Accessibility** and set the ring's color, thickness, and offset. It appears for keyboard and similar input but not for mouse clicks.

= How do I find images that are missing alt text? =

With ExtenderX Pro, open **ExtenderX → Site Enhancements → Accessibility** and use **Alt Text Audit**. It lists media library images with no alternative text and links to each one for editing; results are paginated so it stays fast on large libraries. When writing alt text, describe what the image conveys in context. If an image is purely decorative, leaving it empty is the correct choice.

= Does the accessibility toolbar respect the "reduce motion" system setting? =

Yes. If a visitor's system asks for reduced motion, the toolbar starts with motion already paused, and switching that off will not force animations back on for them. The toolbar itself is keyboard operable and announces changes to screen readers.

= Does ExtenderX work with any WordPress theme? =

Yes. The Site Enhancements module (dark mode, sticky header, maintenance mode, seasonal effects, content protection, marketing tools, and more) works with any WordPress theme — classic or block themes. The Block Editor Extensions work within the Gutenberg editor and are compatible with any theme that supports the block editor.

= Does ExtenderX work with page builders like Elementor or Divi? =

The Site Enhancements tools (dark mode, sticky header, announcement bar, code snippets, seasonal effects, maintenance mode, and so on) work site-wide regardless of which builder or editor you use. The Block Editor Extensions are specific to the WordPress Gutenberg / block editor and do not add animation controls inside Elementor or Divi's own editors.

= How do I add scroll animations to Gutenberg blocks? =

Go to **ExtenderX → Block Editor** and make sure **Entrance Animation** is enabled. Then edit any page or post, select a block, and open the **ExtenderX** panel in the right sidebar. Choose a preset (fade in, slide up, zoom, flip, bounce, and more), set a duration and delay if you like, and publish. The animation fires once when the block scrolls into the visitor's viewport.

= What is the difference between entrance animations, floating animation, and tilt? =

Entrance animations fire once when a block scrolls into view. Floating animation is a continuous looping motion — useful for hero images or feature callouts. Tilt responds to the visitor's cursor position in real time. Only one motion type runs per block at a time.

= What makes ExtenderX different from other animation plugins? =

Most animation plugins only animate their own blocks, or require you to switch to a different page builder. ExtenderX does two things others don't: it adds animations to every block you already use (any Gutenberg block, any plugin), and it bundles a full suite of site-wide tools — dark mode, sticky header, maintenance mode, seasonal effects, code snippets, marketing widgets — so you don't need a separate plugin for each. One dashboard, one plugin, conditional asset loading throughout.

= Does ExtenderX work with Kadence Blocks, Spectra, Stackable, and other third-party blocks? =

Yes. ExtenderX extends the Gutenberg editor globally, so every registered block — Kadence, Spectra, Stackable, Essential Blocks, Ultimate Blocks, Ghostkit, Greenshift, Otter Blocks, and all core WordPress blocks — automatically gets the ExtenderX sidebar panel with all six extensions.

= Will ExtenderX slow down my website? =

No. Every module uses conditional asset loading. Animation scripts only load on pages where at least one block uses an effect. Site enhancement scripts only load when their feature is enabled. Disabled features add zero CSS or JavaScript to any page.

= What is Site-Wide Animations and how is it different from block entrance animations? =

Site-Wide Animations (free, beta) lets you set a single reveal style — such as Flip Y — once from the dashboard, and it applies automatically to your content across every page without manually configuring each block. You can still override it per page or per block inside the editor. Block entrance animations, by contrast, are configured individually per block and give you precise control over each effect.

= How do I enable dark mode on my WordPress site? =

Go to **ExtenderX → Site Enhancements → Appearance** and toggle **Dark Mode** on. Visitors get a floating button to switch between light and dark. The engine is DarkReader and works with any theme.

= How do I put my WordPress site in maintenance mode? =

Go to **ExtenderX → Site Enhancements → Site Access Modes** and enable **Maintenance Mode**. Visitors see a maintenance screen; admins keep full access to the backend.

= How do I create a coming soon page? =

Enable **Coming Soon Mode** under **ExtenderX → Site Enhancements → Site Access Modes**. Visitors see a full-page coming-soon screen before launch; administrators keep full site access.

= How do I add Google Analytics or Google Tag Manager without editing theme files? =

Go to **ExtenderX → Custom Code**, click **Add Snippet**, paste your tracking code, choose **Header** (`<head>`), **Body Open** (after `<body>`), or **Footer** (before `</body>`) as the injection point, and save. Works for Google Analytics, Google Tag Manager, Meta Pixel, AdSense, and verification tags — no theme file edits needed.

= How do I add an announcement bar or notification bar? =

Enable **Announcement Bar** under **ExtenderX → Site Enhancements → Marketing**. Set your message, colors, position, and whether visitors can dismiss it.

= How do I add a sticky header to my WordPress site? =

Enable **Sticky Header** under **ExtenderX → Site Enhancements → Navigation & Scrolling**. Your existing header stays fixed at the top of the viewport as visitors scroll.

= How do I add a scroll progress bar? =

Enable **Scroll Progress Bar** under **ExtenderX → Site Enhancements → Navigation & Scrolling**.

= How do I add a back to top button to my WordPress site? =

Enable **Back to Top Button** under **ExtenderX → Site Enhancements → Navigation & Scrolling**. Pro adds a matching Scroll to Bottom button with the same style options; both can be active simultaneously.

= How do I add a falling snow or seasonal animation effect? =

Go to **ExtenderX → Site Enhancements → Seasonal Effects** and enable **Winter Snow**, **Autumn Leaves**, or **Falling Hearts** (all free). Pro unlocks Spring Petals, Summer Bubbles, Christmas Lights, Fireworks, Halloween Bats, Ramadan Lanterns, and more. All effects respect the visitor's "reduce motion" preference automatically.

= How does content protection work? =

Under **ExtenderX → Site Enhancements → Content Protection**, toggle any combination of: Disable Right Click, Disable Copy, Disable Inspect, Disable Image Drag, or Hide Admin Bar on Frontend. Each operates independently.

= What is Save & Go? =

Save & Go adds a menu to the post editor (block and classic) that saves your post and then jumps to the next step: a new post, the post list, the next or previous post, or the front end. Those five actions are free. Pro adds Save & Duplicate, Save & Return, Save & View in New Window, and Bulk Add for creating many posts from a list of titles.

= Can I add a WhatsApp chat button to my site? =

Yes, with ExtenderX Pro. Enable **Sticky WhatsApp Button** under **Site Enhancements → Marketing**.

= Does ExtenderX include cookie consent and GDPR tools? =

Yes, with ExtenderX Pro. Enable **Cookie Consent** or **GDPR Notice** under **Site Enhancements → Site Access Modes**.

= Does ExtenderX support RTL languages? =

Yes. ExtenderX ships full RTL stylesheet support for Arabic, Hebrew, Persian, Urdu, and other right-to-left languages.

= How do I show or hide a block on mobile, tablet, or desktop? =

In the Block Editor, select a block, open the **ExtenderX** panel, and use **Visibility Control** to toggle visibility per device independently. Visibility is applied with CSS — no duplicate HTML content.

= How do I make an entire block clickable? =

Enable **Wrapper Link** in the ExtenderX panel for any block and enter a URL. The entire block becomes a clickable link on the frontend.

= How do I upgrade to ExtenderX Pro? =

Click **Upgrade to Pro** inside the ExtenderX dashboard, or visit [wpxero.com/plugins/extenderx/pricing](https://wpxero.com/plugins/extenderx/pricing?utm_source=wporg&utm_medium=readme&utm_campaign=faq-upgrade). Install the Pro plugin alongside the free one and activate your licence — your existing settings carry over.

== Screenshots ==

1. **Accessibility toolbar on the front end** – the Dyslexia profile applied in one click: larger text, a readable font and left-aligned text. Visitors open the panel with the floating button or Alt+A.
2. **Low vision profile** – high contrast, 130% text, underlined links and highlighted headings, all from one button. Reset all clears everything.
3. **Dashboard** – turn the accessibility toolbar on with one click, then preview it on your site. Site Enhancements, Block Editor extensions and Custom Code sit below.
4. **Accessibility settings** – Accessibility Toolbar, Skip to Content Link, Enhanced Focus Outlines, Accessibility Statement, and Alt Text Audit, with the toolbar's tool, profile, and branding settings.
5. **Site Enhancements — Appearance** – Dark Mode, Skeleton Loading Screen, and Preloader toggles with per-feature settings.
6. **Site Enhancements — Navigation & Scrolling** – Back to Top, Scroll to Bottom, Sticky Header, Sticky Footer Bar, Scrollbar Styler, and Smooth Scrolling.
7. **Site Enhancements — Motion & Animations (Beta)** – Site-Wide Animations and Scroll-Linked Effects engines with Import/Export for your motion configuration.
8. **Site Enhancements — Seasonal Effects** – Site-wide effects grouped by Seasons, Holidays, Sales, and Ambient; Winter Snow, Autumn Leaves, and Falling Hearts are free.
9. **Site Enhancements — Content Protection** – Disable Right Click, Copy, Inspect, Image Drag, and Hide Admin Bar controls.
10. **Site Enhancements — Marketing** – Announcement Bar, Floating CTA Button, Sticky WhatsApp Button, CTA Pulse Animation, and Global Countdown Banner.
11. **Site Access Modes** – Maintenance Mode, Coming Soon Mode, Age Verification, Cookie Consent, GDPR Notice, and Exit-Intent Popup.
12. **Block Editor Extensions** – Six Gutenberg extensions (Entrance Animation, Floating Animation, Tilt, Visibility Control, Wrapper Link, Tooltip) with individual toggles.
13. **Custom Code Snippets** – Snippet table with type (HTML/CSS/JS), injection location, logic rules, and per-snippet active toggle.

== Changelog ==

= 1.11.2 – November 27, 2026 =
* Fix: Setting Sticky Header Scroll Offset to 0 px now makes the header stick from the first pixel of scroll. Before, it behaved like 100 px and the admin slider jumped back to 100.
* Fix: The same 0 value is now respected by Floating CTA Scroll Offset, Scrollspy Offset, Back to Top and Scroll to Bottom Scroll Speed (0 jumps instantly), and Dark Mode Transition Speed (0 switches instantly).
* Fix: Sticky Header's Animation setting now works. "None" pins the header without any animation, and "Fade In" fades instead of sliding. Before, every choice slid the header in.
* Fix: Sticky Header no longer disappears and slides back in when it sticks while still on screen (for example with a small Scroll Offset). The entrance animation now plays only for a header that had scrolled out of view.
* Fix: Page content no longer jumps up by the header's height when Sticky Header switches on.
* Fix: Sticky Header's "Show on mobile" setting is now respected on small screens.
* Improved: Sticky Header applies straight away when a page loads already scrolled down, without waiting for the next scroll.
* Improved: Sticky Header's entrance animation is skipped for visitors who ask their device to reduce motion.

= 1.11.1 – November 21, 2026 =
* Fix: With the accessibility toolbar on, the Dark Mode switch, Back to Top, Scroll to Bottom, Floating CTA and WhatsApp buttons were hidden underneath its button. They now stack above it.
* Fix: The toolbar's "Pause animations" option and the Seizure-safe profile now also stop the seasonal effects (snow, leaves, hearts and the rest), not only CSS animations.
* Fix: Copy and right-click protection no longer blocks form fields — visitors can select, copy, paste and use spell-check in inputs and text areas again.
* Fix: The Announcement Bar and Countdown Banner no longer hide behind the WordPress admin bar when you are logged in, and no longer run 40 px past the right edge.
* Fix: Updated stylesheets now reach visitors right after a plugin update instead of being served from the browser or page cache.
* Fix: Particle Background no longer blocks clicks — links, buttons and form fields under it work again.
* Fix: Block animations (Entrance, Floating, Tilt), Tooltip, Visibility and Wrapper Link now appear in the block editor as soon as you turn on Gutenberg Blocks, instead of only after saving the Block Editor page.
* Fix: Zoom content (Pro) no longer enlarges the accessibility toolbar off the screen, and the reading guide and mask stay on the pointer while zoomed.
* Fix: The accessibility toolbar button moves above the Cookie Consent / GDPR bar while it is showing, so visitors can reach it straight away.
* Improved: The Dyslexia font tool (Pro) now ships the OpenDyslexic typeface, so every visitor gets it — not only those who have it installed. It is served from your own site and downloaded only when a visitor turns the tool on.
* Improved: A cleaner dashboard — every Site Enhancements section is visible at once, each page shows its title, the layout uses the full width on tablets and small laptops, settings buttons are easier to find, and keyboard focus is clearly visible.
* Improved: PHP snippets (Pro) with a syntax error are refused on save with the line number, instead of saving as active and silently not running.
* Improved: The Accessibility Statement (Pro) lists only the accessibility tools that are switched on.
* Improved: Sticky WhatsApp and Countdown Banner cards say what to fill in when they are on but not yet showing; the Alt Text Audit tells you when the media library has no images.
* Improved: While Maintenance or Coming Soon mode is on, administrators see a reminder in the admin bar that links to its settings.
* Improved: The Dark Mode switch announces its on/off state to screen readers.
* Improved: Updated the Freemius SDK to 2.13.5.

= 1.11.0 – October 30, 2026 =
* New: The dashboard now opens with an Accessibility toolbar card. Turn the toolbar on with one click, then preview it on your site.
* Improved: The plugin is now named "ExtenderX – Accessibility Toolbar, Dark Mode & Site Tools". Nothing about your settings or the plugin's features changes.
* Improved: The WordPress.org review reminder now also appears on sites using the accessibility toolbar, not only those using block animations.

= 1.10.0 – September 19, 2026 =
* New: Save & Go — adds a menu to the post editor (block and classic) that saves and then jumps straight to a new post, the post list, the next or previous post, or the front end. Five actions ship free.
* New (Pro): Three more actions — Save & Duplicate, Save & Return, and Save & View in New Window — plus a choice of default action and control over where the button sits in each editor.
* New (Pro): Bulk Add — paste a list of titles and create them as posts, pages or custom post types in one go, nested under each other where the post type supports it.
* Improved: Removed a duplicate "Saved" indicator that appeared alongside the dashboard's toast notifications.

= 1.9.0 – August 30, 2026 =
* New: Accessibility — a new dashboard tab with tools that let visitors adjust how your site is displayed. Free, and off until you enable it.
* New: Accessibility toolbar — a floating button opening a panel of display controls, grouped into Text, Color, Content and Motion. Covers text size, a readable font, left-aligned text, high contrast, inverted colors, grayscale, a light background, underlined links, highlighted headings, hidden images and paused motion.
* New: Quick profiles — Low vision, Dyslexia, Focus and Seizure safe presets that switch on a matching set of tools in one click, using only the tools you have enabled.
* New: Visitor preferences are remembered from page to page, stored in the visitor's own browser. No cookies, and nothing is sent to a server.
* New: Skip to Content link — lets keyboard visitors jump past your header to the main content, and moves focus properly rather than only scrolling.
* New: Enhanced focus outlines — a configurable ring around whichever element the keyboard is on, for themes that hide the browser default.
* New: Visitors can open the toolbar with Alt+A, and a badge on the button shows how many settings are active.
* New (Pro): Seven more visitor tools — reading guide, reading mask, text spacing, content zoom, a dyslexia-friendly typeface, a large cursor and saturation control.
* New (Pro): Toolbar branding — button and icon colors, icon choice, size, stacking order and hide-on-mobile.
* New (Pro): Accessibility Statement generator — creates an editable draft page describing your accessibility work and how visitors can report problems.
* New (Pro): Alt Text Audit — lists images in your media library with no alternative text, with a link to fix each one.
* Changed: Site-Wide Animations (beta) is now free for everyone. Scroll-Linked Effects and Motion Import/Export remain Pro.
* Improved: Premium features now stay marked with a PRO badge across the whole dashboard, including on licensed sites, so you can see what your licence provides.
* Fixed: Premium features stayed unlocked when the ExtenderX Pro plugin was deactivated. Premium now requires the Pro plugin to be active as well as a valid licence.
* Fixed: With a valid licence but the Pro plugin deactivated, premium features ran on the front end while the dashboard showed them locked behind an upgrade prompt. Both now agree.
* Fixed: Settings migrations could be skipped on sites whose recorded version was already current, leaving a pending update unapplied.

= 1.8.1 – July 22, 2026 =
* New: "New" badge on the Scroll to Bottom Button card in the dashboard.
* Improved: Pro-only toggles (Scroll to Bottom, Skeleton Loader, Preloader, Scrollbar Styler, Smooth Scrolling, Scroll-Linked Effects, and the Seasonal Effects / Marketing / Site Access Modes cards) now link directly to the upgrade page when clicked, rather than appearing as non-interactive disabled controls.

= 1.8.0 – July 14, 2026 =
* New (Pro, Beta): Motion & Animations — Site-Wide Animations applies one reveal style across every page with block-type targeting, stagger delays, and per-page/per-block overrides in the editor. Scroll-Linked Effects adds parallax drift and scroll-progress scrub. Includes Import/Export for your motion configuration.
* New: Seasonal Effects — 18 site-wide effects across Seasons, Holidays, Sales, and Ambient groups. Winter Snow, Autumn Leaves, and Falling Hearts are free; 15 additional effects are Pro.
* New (Pro): Advanced seasonal controls — schedule effects by date range, target specific pages, and customize colors per effect.
* New (Pro): Scroll to Bottom button with the same style presets, icon options, positions, and visibility targeting as Back to Top. Both buttons stack neatly when active simultaneously.
* New (Pro): Custom Code power-ups — PHP snippets, conditional display logic, and snippet scheduling.
* Improved: All seasonal effects automatically honor visitors' "prefers-reduced-motion" system preference.
* Improved: Code snippets now use a dedicated database table for faster, more reliable storage. Existing snippets migrate automatically.
* Improved: Back to Top settings panel now controls both scroll buttons to keep options in sync.
* Performance: Settings storage split per module; no longer autoloads on every page request, reducing memory use site-wide.

= 1.7.1 – June 21, 2026 =
* Improved: Expanded plugin listing to make free site tools easier to discover.
* Added: New FAQ entries for seasonal effects, maintenance mode, coming soon pages, announcement bars, and adding tracking scripts without editing theme files.
* Fixed: Corrected free vs. Pro feature split — Smart Scroll and three seasonal effects are free; Smooth Scrolling (Lenis) is Pro.

= 1.7.0 – June 20, 2026 =
* New: Insert Headers & Footers + Custom Code Snippets — manage HTML, CSS, and JavaScript snippets with per-snippet toggles.
* New: Site Access Modes — Maintenance Mode, Coming Soon Mode, Age Verification (Pro), Cookie Consent (Pro), GDPR Notice (Pro), Exit-Intent Popup (Pro).
* New: Redesigned card-based admin dashboard.
* Improved: Settings batch-save on rapid toggles with a clean non-duplicating notice.
* Improved: Consistent notification styling across all dashboard screens.
* Improved: License status reflected in the Upgrade button — Pro users see "Pro Active" badge.
* Improved: ARIA labels on feature toggles and current-page indicator in sidebar navigation.
* Performance: Removed unused code from the settings dashboard.

= 1.6.6 – June 06, 2026 =
* Improved: Animation-first plugin listing for better discoverability on WordPress.org.
* Added: Quick Start checklist and editor deep-link in the dashboard.
* Added: Review prompt and one-time block editor onboarding notice for new users.
* Added: Documentation hub with block-library compatibility guides.
* Removed: Scroll Effects "Coming Soon" placeholder — use Entrance Animations instead.

= 1.6.5 – May 11, 2026 =
* Improved: Floating animations block wrapper and editor panel.
* Improved: Toggle control visuals and reset button styling in block extension panels.
* Improved: Tooltip rendering behavior and shared extension helpers.

= 1.6.4 – April 11, 2026 =
* Improved: Compatibility with the latest WordPress and PHP versions.
* Fixed: Several features not functioning correctly in certain environments.

= 1.6.3 – April 08, 2026 =
* Improved: Memory management and server resource optimization.
* Improved: Settings page interface redesign.

= 1.6.2 – February 24, 2026 =
* Fixed: Sticky Header and Sticky Footer not working correctly on certain themes.
* Fixed: Gradient color rendering in the Scrollbar Styler and Scroll Progress Bar.
* Fixed: Hide Admin Bar on Frontend not applying for logged-in users.

= 1.6.1 – February 22, 2026 =
* Improved: Dark mode admin dashboard styling.
* Improved: Dark mode shortcode output and color settings.
* Improved: Top center and bottom center positions for the Back to Top button.
* Improved: Color settings for sticky header and footer.
* Fixed: Scrollbar Styler rendering issues.

= 1.6.0 – February 17, 2026 =
* Added: Back to Top button.
* Added: Sticky Header — navigation fixed at the top on scroll.
* Added: Sticky Footer Bar — persistent footer strip.
* Added: Smooth Scrolling (Pro) — Lenis-powered page scrolling.
* Added: Scrollspy — active nav highlighting based on scroll position.
* Added: Scroll Progress Bar — reading progress indicator.

Older versions: see [changelog.txt](https://plugins.svn.wordpress.org/extenderx/trunk/changelog.txt).

== Upgrade Notice ==

= 1.11.2 =
Fixes Sticky Header: a Scroll Offset of 0 now sticks from the first pixel, the Animation setting (None / Fade In / Slide Down) is honoured, and the header no longer flashes or makes the page jump when it sticks.

= 1.11.1 =
Fixes floating buttons hidden under the accessibility toolbar, block animations missing from the editor, and Particle Background blocking clicks; "Pause animations" now stops seasonal effects and copy protection no longer blocks form fields.

= 1.11.0 =
Adds a dashboard card that turns the free accessibility toolbar on in one click, with a link to preview it on your site. The plugin also has a new name; your settings are unchanged.

= 1.10.0 =
Adds Save & Go: a post-editor menu to save and jump straight to a new post, the list, the next/previous post, or the front end. Five actions are free; Save & Duplicate, Save & Return, Save & View in New Window and Bulk Add are Pro.

= 1.9.0 =
Adds a free Accessibility module: a visitor toolbar with 11 display controls, quick profiles, an Alt+A shortcut and remembered preferences, plus a skip-to-content link and enhanced focus outlines. Site-Wide Animations (beta) is now free. Premium features now require the ExtenderX Pro plugin to be active as well as a valid licence.

= 1.8.0 =
Major release: 18 seasonal effects, Motion & Animations with site-wide reveal and scroll-linked parallax (Pro, beta), Scroll to Bottom button (Pro), PHP snippets with conditional logic and scheduling (Pro), faster snippet storage, lighter settings loading. Recommended for all users.

== License & Disclaimer ==

This plugin is licensed under GPL v2 or later. No warranties are provided. You are free to modify and distribute it under the same license.
