# EveTech Website - Copilot Instructions

## Project Overview
Static corporate website for EveTech (telecommunications/technology company) built with HTML, CSS, JavaScript, and PHP (contact form). Hosted on Laragon (local development).

## Architecture

### File Structure
- **HTML Pages**: Root-level static pages (`index.html`, `about-us.html`, `contact-us.html`, etc.)
- **CSS**: Located in `css/` with layered architecture:
  - `bootstrap.min.css` → Base framework
  - `base.css` → CSS variables, typography, general styles
  - `shortcode.css` → Reusable component styles (`.pbmit-*` prefix)
  - `style.css` → Page-specific styles
  - `responsive.css` → Breakpoint-based responsive rules
- **JavaScript**: Located in `js/` with `scripts.js` as the main custom script
- **Images**: Organized by page/section in `images/homepage-*`, `images/service-detail/`, etc.

### CSS Architecture & Naming Conventions
- **BEM-like prefix**: All custom components use `.pbmit-` prefix (e.g., `.pbmit-slider-area`, `.pbmit-header-content`)
- **CSS Variables**: Defined in `base.css` under `:root` - always use these for colors and typography:
  ```css
  --pbmit-global-color: #363533;
  --pbmit-secondary-color: #363533;
  --pbmit-white-color: #ffffff;
  --pbmit-link-color-hover: #e0bb83;
  --pbmit-heading-typography-font-family: "IBM Plex Sans", sans-serif;
  --pbmit-body-typography-font-family: "Schibsted Grotesk", sans-serif;
  ```

### Header Styles
Two header variants exist:
- `header-style-1`: Standard header with pre-header bar (used on inner pages)
- `header-style-2`: Overlay header without pre-header (used on homepage)

## Key Libraries & Dependencies
- **Bootstrap 5** (grid, utilities)
- **Swiper.js** - Carousels/sliders configured via data attributes
- **AOS.js** - Scroll animations
- **GSAP + ScrollTrigger** - Advanced animations
- **PHPMailer** - Contact form email delivery (requires SMTP config in `send.php`)

## Contact Form Integration
The contact form in `contact-us.html` posts to `send.php`:
- Uses PHPMailer with Gmail SMTP (requires app-specific password)
- Form fields defined in `$field_list` and `$required_fields` arrays
- Returns HTML alerts for success/error states

## Development Workflow

### Local Development
Project runs on Laragon. Open HTML files directly or access via configured virtual host.

### Adding New Pages
1. Copy an existing page as template (e.g., `about-us.html`)
2. Maintain consistent CSS link order in `<head>`
3. Include header and footer sections from template
4. Add page-specific styles in `<style>` tag or `style.css`

### Adding New Components
1. Use `.pbmit-` prefix for new component classes
2. Add reusable component styles to `shortcode.css`
3. Add page-specific styles to `style.css`
4. Add responsive rules to `responsive.css` following existing breakpoints

## Common Patterns

### Swiper Slider Configuration
Sliders use data attributes for configuration:
```html
<div class="swiper-slider" data-autoplay="true" data-loop="true" data-dots="true" data-columns="3">
```

### Section Structure
```html
<section class="section-lg pbmit-bg-color-light">
  <div class="container">
    <div class="pbmit-heading-subheading">
      <h4 class="pbmit-subtitle">Subtitle</h4>
      <h2 class="pbmit-title">Main Title</h2>
    </div>
    <!-- Content -->
  </div>
</section>
```

### Navigation Menu
Navigation items in header must be updated in ALL HTML files when adding/modifying pages:
```html
<ul class="navigation clearfix">
  <li class="active"><a href="index.html">Home</a></li>
  <!-- ... -->
</ul>
```

## Important Notes
- **No build process** - Static files served directly
- **Image optimization** - Place appropriately sized images in correct `images/` subdirectory
- **Browser support** - CSS uses modern features; ensure fallbacks if legacy support needed
- **Form security** - `send.php` contains SMTP credentials; never commit real credentials
