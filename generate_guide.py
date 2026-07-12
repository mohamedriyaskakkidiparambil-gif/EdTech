from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    PageBreak, HRFlowable, Preformatted
)
from reportlab.lib.enums import TA_LEFT, TA_CENTER
import os

OUTPUT = "/Users/riyas/Projects/FL/Ranees/EdTech/Moodle_Theme_Developer_Guide.pdf"

# ── Colours ────────────────────────────────────────────────────────────────
ORANGE   = colors.HexColor("#F98012")
DARK     = colors.HexColor("#1a1a2e")
LIGHT_BG = colors.HexColor("#f5f5f5")
CODE_BG  = colors.HexColor("#1e1e2e")
CODE_FG  = colors.HexColor("#cdd6f4")
MUTED    = colors.HexColor("#6b7280")
WHITE    = colors.white

# ── Document ───────────────────────────────────────────────────────────────
doc = SimpleDocTemplate(
    OUTPUT,
    pagesize=A4,
    leftMargin=2*cm, rightMargin=2*cm,
    topMargin=2.5*cm, bottomMargin=2.5*cm,
    title="Moodle Theme Developer Guide",
    author="Ranees EdTech",
)

W, H = A4
styles = getSampleStyleSheet()

# ── Custom styles ──────────────────────────────────────────────────────────
def S(name, **kw):
    return ParagraphStyle(name, **kw)

sTitle = S("sTitle",
    fontSize=32, leading=40, textColor=WHITE,
    fontName="Helvetica-Bold", alignment=TA_CENTER, spaceAfter=6)

sSubtitle = S("sSubtitle",
    fontSize=14, leading=20, textColor=colors.HexColor("#fde68a"),
    fontName="Helvetica", alignment=TA_CENTER, spaceAfter=4)

sChapter = S("sChapter",
    fontSize=20, leading=26, textColor=ORANGE,
    fontName="Helvetica-Bold", spaceBefore=18, spaceAfter=8)

sSection = S("sSection",
    fontSize=13, leading=18, textColor=DARK,
    fontName="Helvetica-Bold", spaceBefore=12, spaceAfter=4)

sBody = S("sBody",
    fontSize=10, leading=16, textColor=colors.HexColor("#1f2937"),
    fontName="Helvetica", spaceAfter=6)

sBullet = S("sBullet",
    fontSize=10, leading=15, textColor=colors.HexColor("#374151"),
    fontName="Helvetica", leftIndent=16, spaceAfter=3,
    bulletIndent=6, bulletFontName="Helvetica", bulletFontSize=10)

sCode = S("sCode",
    fontSize=8.5, leading=13, textColor=CODE_FG,
    fontName="Courier", backColor=CODE_BG,
    leftIndent=10, rightIndent=10,
    spaceAfter=10, spaceBefore=4,
    borderPadding=(8, 10, 8, 10))

sNote = S("sNote",
    fontSize=9, leading=14, textColor=colors.HexColor("#92400e"),
    fontName="Helvetica-Oblique", leftIndent=12, spaceAfter=8)

sTOC = S("sTOC",
    fontSize=11, leading=18, textColor=DARK,
    fontName="Helvetica", leftIndent=8, spaceAfter=2)

sTOCsub = S("sTOCsub",
    fontSize=10, leading=16, textColor=MUTED,
    fontName="Helvetica", leftIndent=24, spaceAfter=1)

story = []

# ══════════════════════════════════════════════════════════════════════════
# COVER PAGE
# ══════════════════════════════════════════════════════════════════════════
def cover_page(canvas, doc):
    canvas.saveState()
    # Dark gradient background
    canvas.setFillColor(DARK)
    canvas.rect(0, 0, W, H, fill=1, stroke=0)
    # Orange accent bar top
    canvas.setFillColor(ORANGE)
    canvas.rect(0, H - 1.2*cm, W, 1.2*cm, fill=1, stroke=0)
    # Orange accent bar bottom
    canvas.rect(0, 0, W, 0.8*cm, fill=1, stroke=0)
    # Decorative circle
    canvas.setFillColor(colors.HexColor("#16213e"))
    canvas.circle(W - 1*cm, H * 0.6, 8*cm, fill=1, stroke=0)
    canvas.setFillColor(colors.HexColor("#0f3460"))
    canvas.circle(W - 1*cm, H * 0.6, 5*cm, fill=1, stroke=0)
    canvas.restoreState()

story.append(Spacer(1, 3.5*cm))
story.append(Paragraph("Moodle Theme", sTitle))
story.append(Paragraph("Developer Guide", sTitle))
story.append(Spacer(1, 0.5*cm))
story.append(Paragraph("Mustache · SCSS · Child Themes · PHP Renderers", sSubtitle))
story.append(Spacer(1, 1*cm))

# Cover info box
cover_data = [
    ["Project", "Ranees EdTech"],
    ["Platform", "Moodle 4.5 (Self-hosted, Docker)"],
    ["Base Theme", "Boost"],
    ["Version", "1.0"],
]
cover_table = Table(cover_data, colWidths=[4*cm, 10*cm])
cover_table.setStyle(TableStyle([
    ("BACKGROUND",   (0,0), (0,-1), colors.HexColor("#0f3460")),
    ("BACKGROUND",   (1,0), (1,-1), colors.HexColor("#16213e")),
    ("TEXTCOLOR",    (0,0), (0,-1), ORANGE),
    ("TEXTCOLOR",    (1,0), (1,-1), WHITE),
    ("FONTNAME",     (0,0), (0,-1), "Helvetica-Bold"),
    ("FONTNAME",     (1,0), (1,-1), "Helvetica"),
    ("FONTSIZE",     (0,0), (-1,-1), 10),
    ("PADDING",      (0,0), (-1,-1), 10),
    ("ROWBACKGROUNDS", (0,0), (-1,-1), [colors.HexColor("#16213e"), colors.HexColor("#1a1a2e")]),
    ("GRID",         (0,0), (-1,-1), 0.3, colors.HexColor("#374151")),
]))
story.append(cover_table)
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# Helper functions
# ══════════════════════════════════════════════════════════════════════════
def chapter(text):
    story.append(HRFlowable(width="100%", thickness=3, color=ORANGE, spaceAfter=6))
    story.append(Paragraph(text, sChapter))

def section(text):
    story.append(Paragraph(text, sSection))

def body(text):
    story.append(Paragraph(text, sBody))

def bullet(text):
    story.append(Paragraph(f"• {text}", sBullet))

def code(text):
    story.append(Preformatted(text, sCode))

def note(text):
    story.append(Paragraph(f"⚠ {text}", sNote))

def spacer(h=0.3):
    story.append(Spacer(1, h*cm))

def table(data, col_widths=None, header=True):
    t = Table(data, colWidths=col_widths)
    style = [
        ("FONTNAME",   (0,0), (-1,-1), "Helvetica"),
        ("FONTSIZE",   (0,0), (-1,-1), 9),
        ("PADDING",    (0,0), (-1,-1), 7),
        ("GRID",       (0,0), (-1,-1), 0.4, colors.HexColor("#d1d5db")),
        ("ROWBACKGROUNDS", (0,0), (-1,-1), [WHITE, LIGHT_BG]),
    ]
    if header:
        style += [
            ("BACKGROUND", (0,0), (-1,0), DARK),
            ("TEXTCOLOR",  (0,0), (-1,0), WHITE),
            ("FONTNAME",   (0,0), (-1,0), "Helvetica-Bold"),
        ]
    t.setStyle(TableStyle(style))
    story.append(t)
    spacer(0.4)

# ══════════════════════════════════════════════════════════════════════════
# TABLE OF CONTENTS
# ══════════════════════════════════════════════════════════════════════════
story.append(Paragraph("Table of Contents", sChapter))
spacer(0.2)
toc_items = [
    ("1.", "Introduction to Moodle Theming", [
        "What is a Moodle theme",
        "Theme inheritance (parent/child)",
        "The Boost theme as base",
        "How Moodle compiles SCSS and renders templates",
    ]),
    ("2.", "Child Theme File Structure", [
        "Every file explained with purpose",
    ]),
    ("3.", "Creating Your First Child Theme", [
        "Step-by-step from zero to active theme",
    ]),
    ("4.", "SCSS Customization", [
        "pre.scss, post.scss, Bootstrap variables",
    ]),
    ("5.", "Mustache Templates", [
        "Syntax, overrides, key templates, data context",
    ]),
    ("6.", "PHP Renderer Overrides", [
        "core_renderer.php extension",
    ]),
    ("7.", "JavaScript — AMD Modules", [
        "RequireJS, creating modules, third-party libs",
    ]),
    ("8.", "Development Workflow (Docker)", [
        "Cache purging, designer mode, volume mounting",
    ]),
    ("9.", "Common Mistakes", []),
    ("10.", "Quick Reference", [
        "Minimal file templates, URLs, CLI commands",
    ]),
]
for num, title, subs in toc_items:
    story.append(Paragraph(f"<b>{num}</b>  {title}", sTOC))
    for sub in subs:
        story.append(Paragraph(f"— {sub}", sTOCsub))
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 1. INTRODUCTION
# ══════════════════════════════════════════════════════════════════════════
chapter("1. Introduction to Moodle Theming")

section("1.1 What is a Moodle Theme?")
body("A Moodle theme is a plugin that controls the visual presentation of your Moodle site. It defines the HTML structure (via Mustache templates), styling (via SCSS/CSS), and JavaScript behaviour of every page students and teachers see.")
body("Themes live in the <b>/theme/</b> directory of your Moodle installation. Each theme is a self-contained folder with a strict naming convention: lowercase letters and underscores only (e.g. <b>theme_ranees</b>).")

section("1.2 Theme Inheritance — Parent / Child")
body("Moodle themes support inheritance. A child theme declares one or more parent themes in its <b>config.php</b>. When Moodle looks for a template, SCSS, or renderer, it searches the child first, then walks up the parent chain.")
body("This means your child theme only needs to contain the files you actually want to override — everything else is inherited automatically from the parent.")

table([
    ["Layer", "Who provides it", "Override in child?"],
    ["Bootstrap 4 SCSS",   "theme/boost",    "Yes — via pre.scss variables"],
    ["Moodle core SCSS",   "theme/boost",    "Yes — via post.scss"],
    ["HTML templates",     "theme/boost",    "Yes — copy & edit in templates/"],
    ["PHP renderers",      "lib/outputrenderers.php", "Yes — extend core_renderer"],
    ["JavaScript",         "theme/boost/amd","Yes — add your own modules"],
], col_widths=[4.5*cm, 5.5*cm, 5*cm])

section("1.3 The Boost Theme")
body("Boost is Moodle's modern default theme, built on Bootstrap 4. All new custom themes should extend Boost. It provides:")
bullet("A responsive two-column layout with collapsible drawers")
bullet("Bootstrap 4 grid, components, and utilities")
bullet("Full SCSS source (not just compiled CSS)")
bullet("Mustache templates for every page region")
bullet("A clean AMD JavaScript module system")
spacer()
note("Never edit files inside theme/boost/ directly. Your changes will be lost on every Moodle upgrade. Always create a child theme.")

section("1.4 How Moodle Compiles SCSS and Renders Templates")
body("<b>SCSS Compilation:</b> Moodle compiles SCSS server-side using PHP (the ScssPhp library). The compilation order is:")
bullet("1. Your pre.scss (variable overrides)")
bullet("2. Bootstrap 4 source")
bullet("3. Moodle core SCSS")
bullet("4. Your post.scss (custom additions)")
body("The compiled CSS is cached. You must purge caches after any SCSS change.")
spacer(0.2)
body("<b>Template Rendering:</b> When a page loads, Moodle's PHP layout controller (e.g. layout/drawers.php) builds a data context array and passes it to a Mustache template. The template is rendered server-side and sent to the browser as plain HTML.")
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 2. FILE STRUCTURE
# ══════════════════════════════════════════════════════════════════════════
chapter("2. Child Theme File Structure")
body("Here is the complete file structure of a child theme named <b>ranees</b>. Files marked REQUIRED must exist for the theme to load.")

code("""theme/ranees/
├── config.php              ← REQUIRED: parents, layouts, SCSS config
├── version.php             ← REQUIRED: version number for upgrades
├── lib.php                 ← REQUIRED: pre/post SCSS hook functions
├── settings.php            ← Optional: admin settings (logo, colour pickers)
│
├── scss/
│   ├── pre.scss            ← Bootstrap variable overrides (loaded first)
│   └── post.scss           ← Custom CSS added after compilation
│
├── templates/              ← Mustache HTML overrides (copy from boost)
│   ├── navbar.mustache
│   ├── login.mustache
│   ├── footer.mustache
│   └── drawers.mustache
│
├── layout/                 ← PHP layout controllers
│   └── drawers.php
│
├── classes/
│   └── output/
│       └── core_renderer.php  ← PHP renderer overrides
│
├── pix/
│   └── favicon.ico         ← Your site favicon
│
├── lang/
│   └── en/
│       └── theme_ranees.php  ← Theme name string (REQUIRED)
│
└── amd/
    ├── src/                ← JS source (ES modules)
    │   └── mycustom.js
    └── build/              ← Compiled JS (auto-generated by Grunt)
        └── mycustom.min.js""")

spacer(0.3)
table([
    ["File", "Purpose"],
    ["config.php",          "Registers the theme: parent, layouts, SCSS entry points, regions"],
    ["version.php",         "Theme version number — Moodle uses this to detect upgrades"],
    ["lib.php",             "PHP functions that inject pre.scss and post.scss into the compiler"],
    ["settings.php",        "Adds admin settings (logo upload, colour picker, font selector)"],
    ["scss/pre.scss",       "Loaded before Bootstrap — override $primary, $font-family-base, etc."],
    ["scss/post.scss",      "Loaded after everything — add any custom CSS rules here"],
    ["templates/*.mustache","HTML template overrides — only include templates you change"],
    ["layout/*.php",        "Controls which template + context is used for each page type"],
    ["classes/output/core_renderer.php", "Override how Moodle generates specific HTML fragments"],
    ["pix/",                "Images: favicon.ico, logo, screenshots"],
    ["lang/en/theme_*.php", "Language strings — minimum: $string['pluginname'] = 'Your Theme'"],
    ["amd/src/",            "JavaScript AMD modules — your custom JS goes here"],
], col_widths=[5.5*cm, 10.5*cm])
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 3. CREATING YOUR FIRST CHILD THEME
# ══════════════════════════════════════════════════════════════════════════
chapter("3. Creating Your First Child Theme (Step by Step)")

section("Step 1 — Create the Theme Directory")
body("Inside your Moodle installation, create the theme folder. In your Docker setup, the Moodle source is at <b>/var/www/html/</b>.")
code("""docker exec -it moodle_app bash
mkdir -p /var/www/html/theme/ranees/scss
mkdir -p /var/www/html/theme/ranees/lang/en
mkdir -p /var/www/html/theme/ranees/pix""")

section("Step 2 — config.php")
body("This is the most important file. It tells Moodle everything about your theme.")
code("""<?php
// theme/ranees/config.php

defined('MOODLE_INTERNAL') || die();

$THEME->name        = 'ranees';
$THEME->parents     = ['boost'];          // inherit from Boost
$THEME->sheets      = [];                 // no plain CSS — we use SCSS
$THEME->usefallback = true;

// SCSS entry point functions from lib.php
$THEME->scss = function($theme) {
    return theme_ranees_get_main_scss_content($theme);
};

// Layouts — reuse Boost's layout files
$THEME->layouts = [
    'base'        => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'standard'    => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'course'      => ['file' => 'drawers.php', 'regions' => ['side-pre'], 'defaultregion' => 'side-pre'],
    'coursecategory' => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'incourse'    => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'frontpage'   => ['file' => 'drawers.php', 'regions' => ['side-pre'], 'defaultregion' => 'side-pre'],
    'admin'       => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'mydashboard' => ['file' => 'drawers.php', 'regions' => ['side-pre'], 'defaultregion' => 'side-pre'],
    'login'       => ['file' => 'login.php',   'regions' => [], 'options' => ['nofooter' => true, 'nonavbar' => false]],
    'popup'       => ['file' => 'columns1.php', 'regions' => []],
    'embedded'    => ['file' => 'embedded.php', 'regions' => []],
    'maintenance' => ['file' => 'maintenance.php', 'regions' => []],
    'print'       => ['file' => 'columns1.php', 'regions' => []],
    'redirect'    => ['file' => 'embedded.php', 'regions' => []],
    'report'      => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'secure'      => ['file' => 'secure.php', 'regions' => []],
];

$THEME->enable_dock = false;
$THEME->extrascsscallback   = 'theme_ranees_get_extra_scss';
$THEME->prescsscallback     = 'theme_ranees_get_pre_scss';
$THEME->scss_compiler_class = 'theme_boost\\autoprefixer';
$THEME->rendererfactory     = 'theme_overridden_renderer_factory';
$THEME->requiredblocks      = '';
$THEME->addblockposition    = BLOCK_ADDBLOCK_POSITION_FLATNAV;""")

section("Step 3 — version.php")
code("""<?php
// theme/ranees/version.php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2024042800;   // YYYYMMDDXX format
$plugin->requires  = 2023042400;   // Minimum Moodle version (4.2)
$plugin->component = 'theme_ranees';""")

section("Step 4 — lib.php")
body("These three functions are called by Moodle's SCSS compiler. They inject your SCSS into the compilation pipeline.")
code("""<?php
// theme/ranees/lib.php

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the main SCSS content — loads Boost's preset as the base.
 */
function theme_ranees_get_main_scss_content($theme) {
    global $CFG;
    $scss = '';
    // Load Boost's main preset as base
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();
    if ($filename && ($file = $fs->get_file(context_system::instance()->id,
            'theme_ranees', 'preset', 0, '/', $filename))) {
        $scss .= $file->get_content();
    } else {
        // Default to Boost's preset
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }
    return $scss;
}

/**
 * Returns SCSS to inject BEFORE Bootstrap compilation.
 * Use this to override Bootstrap variables.
 */
function theme_ranees_get_pre_scss($theme) {
    global $CFG;
    $scss = '';
    $scss .= file_get_contents($CFG->dirroot . '/theme/ranees/scss/pre.scss');
    return $scss;
}

/**
 * Returns SCSS to inject AFTER compilation.
 * Use this to add custom rules on top.
 */
function theme_ranees_get_extra_scss($theme) {
    global $CFG;
    $scss = '';
    $scss .= file_get_contents($CFG->dirroot . '/theme/ranees/scss/post.scss');
    return $scss;
}""")

section("Step 5 — Language File (Required)")
code("""<?php
// theme/ranees/lang/en/theme_ranees.php

$string['pluginname'] = 'Ranees EdTech';
$string['choosereadme'] = 'A custom theme for Ranees EdTech, built on Boost.';""")

section("Step 6 — Activate the Theme")
body("Go to <b>Site administration → Appearance → Themes → Theme selector</b> and choose Ranees EdTech. Or use the CLI:")
code("""docker exec moodle_app php /var/www/html/admin/cli/cfg.php \\
    --name=theme --set=ranees""")

section("Step 7 — Purge Caches")
code("""docker exec moodle_app php /var/www/html/admin/cli/purge_caches.php""")
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 4. SCSS CUSTOMIZATION
# ══════════════════════════════════════════════════════════════════════════
chapter("4. SCSS Customization")

section("4.1 pre.scss — Override Bootstrap Variables")
body("Variables in pre.scss are injected before Bootstrap compiles, so they override Bootstrap's own defaults. This is where you define your brand colours, fonts, and spacing.")
code("""// theme/ranees/scss/pre.scss

// ── Brand colours ──────────────────────────────────────
$primary:         #F98012;   // Moodle's $primary maps to Bootstrap's $primary
$secondary:       #1a1a2e;
$success:         #22c55e;
$danger:          #ef4444;
$warning:         #f59e0b;
$info:            #3b82f6;

// ── Typography ─────────────────────────────────────────
$font-family-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
$font-family-base:       $font-family-sans-serif;
$font-size-base:         1rem;       // 16px
$line-height-base:       1.6;

// ── Spacing ────────────────────────────────────────────
$spacer: 1rem;

// ── Border radius ──────────────────────────────────────
$border-radius:    0.5rem;
$border-radius-lg: 0.75rem;
$border-radius-sm: 0.25rem;

// ── Navbar ─────────────────────────────────────────────
$navbar-dark-color:        rgba(255,255,255,.85);
$navbar-dark-active-color: #fff;
$navbar-padding-y:         0.75rem;

// ── Cards ──────────────────────────────────────────────
$card-border-radius:       $border-radius;
$card-cap-bg:              transparent;
$card-border-color:        rgba(0,0,0,.08);

// ── Buttons ────────────────────────────────────────────
$btn-border-radius:        $border-radius;
$btn-font-weight:          600;
$btn-padding-x:            1.25rem;""")

section("4.2 post.scss — Custom CSS")
body("post.scss is loaded after all Bootstrap and Moodle SCSS. Add your own rules here. You have full access to Bootstrap 4 variables and mixins.")
code("""// theme/ranees/scss/post.scss

// ── Custom Google Font import ───────────────────────────
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

// ── Navbar customization ───────────────────────────────
.navbar {
    background: linear-gradient(135deg, $secondary 0%, darken($secondary, 5%) 100%);
    border-bottom: 3px solid $primary;
    box-shadow: 0 2px 12px rgba(0,0,0,.15);
}

// ── Course cards ───────────────────────────────────────
.card {
    transition: transform .2s ease, box-shadow .2s ease;
    &:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
}

// ── Primary button ─────────────────────────────────────
.btn-primary {
    background: linear-gradient(135deg, $primary, darken($primary, 8%));
    border: none;
    letter-spacing: .02em;
    &:hover {
        background: linear-gradient(135deg, darken($primary,5%), darken($primary,12%));
    }
}

// ── Login page ─────────────────────────────────────────
#page-login-index {
    background: linear-gradient(135deg, $secondary 0%, #16213e 100%);
    .card {
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
    }
}

// ── Footer ─────────────────────────────────────────────
#page-footer {
    background: $secondary;
    color: rgba(255,255,255,.7);
    border-top: 3px solid $primary;
    a { color: $primary; }
}""")

section("4.3 Common Bootstrap 4 Variables Reference")
table([
    ["Variable", "Default", "What it controls"],
    ["$primary",              "#007bff", "Primary colour — buttons, links, focus rings"],
    ["$secondary",            "#6c757d", "Secondary colour"],
    ["$font-family-base",     "system fonts", "Body font family"],
    ["$font-size-base",       "1rem",    "Base font size (16px)"],
    ["$line-height-base",     "1.5",     "Body line height"],
    ["$border-radius",        ".25rem",  "Default border radius"],
    ["$grid-gutter-width",    "30px",    "Column gutter width"],
    ["$navbar-height",        "—",       "Navbar height"],
    ["$headings-font-weight", "500",     "h1–h6 font weight"],
    ["$link-color",           "$primary","Hyperlink colour"],
    ["$body-bg",              "#fff",    "Page background colour"],
    ["$body-color",           "#212529", "Default text colour"],
], col_widths=[5.5*cm, 3.5*cm, 7*cm])
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 5. MUSTACHE TEMPLATES
# ══════════════════════════════════════════════════════════════════════════
chapter("5. Mustache Templates")

section("5.1 Mustache Syntax")
body("Moodle uses the Mustache templating language. Templates are plain HTML files with special tags for dynamic content.")
table([
    ["Syntax", "Meaning"],
    ["{{variable}}",          "Output the value of a variable (HTML-escaped)"],
    ["{{{variable}}}",        "Output raw HTML (not escaped — use carefully)"],
    ["{{#block}} ... {{/block}}", "Render if block is truthy, or loop if block is an array"],
    ["{{^block}} ... {{/block}}", "Render if block is falsy (inverse/else block)"],
    ["{{>partial}}",          "Include another template (partial)"],
    ["{{! comment }}",        "Comment — not rendered in output"],
    ["{{#str}} key, component {{/str}}", "Moodle string helper — outputs language string"],
    ["{{#pix}} icon, component {{/pix}}", "Moodle icon helper — outputs an icon"],
], col_widths=[6*cm, 10*cm])

section("5.2 How to Override a Template")
body("1. Find the template you want to change in <b>theme/boost/templates/</b>")
body("2. Copy it into your theme at the same relative path")
body("3. Edit your copy — Moodle will use yours instead")
code("""# Example: override the navbar
docker exec moodle_app bash -c "
  cp /var/www/html/theme/boost/templates/navbar.mustache \\
     /var/www/html/theme/ranees/templates/navbar.mustache
"
# Then purge caches
docker exec moodle_app php /var/www/html/admin/cli/purge_caches.php""")
note("Only copy templates you actually need to change. Unused copies become maintenance debt — future Moodle upgrades may change the original template's context, breaking your override.")

section("5.3 Key Templates to Know")
table([
    ["Template", "Controls"],
    ["drawers.mustache",   "Main page layout — header, sidebars, content area, footer"],
    ["navbar.mustache",    "Top navigation bar — logo, primary nav, user menu"],
    ["login.mustache",     "Login page HTML structure"],
    ["footer.mustache",    "Page footer — links, Moodle version text"],
    ["head.mustache",      "The HTML <head> — meta tags, CSS links"],
    ["columns1.mustache",  "Single-column layout (popup pages)"],
    ["columns2.mustache",  "Two-column layout (legacy)"],
    ["drawer.mustache",    "Left sidebar drawer (course index)"],
    ["blocks-drawer.mustache", "Right sidebar drawer (blocks)"],
    ["navbar-secure.mustache", "Navbar for quiz/secure pages"],
], col_widths=[6*cm, 10*cm])

section("5.4 Example — Custom Navbar Template")
code("""{{! theme/ranees/templates/navbar.mustache }}
<nav class="navbar navbar-expand navbar-dark">
    <div class="container-fluid">

        {{! Logo / Site name }}
        <a class="navbar-brand" href="{{config.wwwroot}}">
            <img src="{{config.wwwroot}}/theme/ranees/pix/logo.png"
                 alt="{{sitename}}" height="40">
        </a>

        {{! Primary navigation }}
        {{#primarymoremenu}}
            {{> core/moremenu}}
        {{/primarymoremenu}}

        {{! Right side — user menu, notifications }}
        <div class="ml-auto d-flex align-items-center">
            {{#usermenu}}
                {{> core/user_menu}}
            {{/usermenu}}
        </div>

    </div>
</nav>""")

section("5.5 Moodle Template Helpers")
code("""{{! Output a language string }}
{{#str}} welcome, core {{/str}}

{{! Output a string with parameter }}
{{#str}} greetings, moodle, {{username}} {{/str}}

{{! Output a pix icon }}
{{#pix}} i/course, core, Course icon {{/pix}}

{{! Format a date }}
{{#userdate}} {{time}}, {{#str}} strftimedatefullshort, core_langconfig {{/str}} {{/userdate}}

{{! Output user picture }}
{{> core/user_picture}}

{{! Conditional block }}
{{#loggedin}}
    <p>Welcome back, {{fullname}}!</p>
{{/loggedin}}
{{^loggedin}}
    <p><a href="{{loginurl}}">Sign in</a></p>
{{/loggedin}}""")
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 6. PHP RENDERER OVERRIDES
# ══════════════════════════════════════════════════════════════════════════
chapter("6. PHP Renderer Overrides")

section("6.1 What is core_renderer?")
body("Some HTML in Moodle is generated directly by PHP renderer classes, not Mustache templates. The main one is <b>core_renderer</b> in <b>lib/outputrenderers.php</b>. It handles things like form elements, notification boxes, user avatars, and navigation.")
body("To override these, you extend the renderer in your theme's <b>classes/output/core_renderer.php</b>.")

section("6.2 Creating the Renderer Override")
code("""<?php
// theme/ranees/classes/output/core_renderer.php

namespace theme_ranees\\output;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

class core_renderer extends \\theme_boost\\output\\core_renderer {

    /**
     * Override the user avatar to add a custom CSS class.
     */
    public function user_picture(\\stdClass $user, array $options = null) {
        $options = (array) $options;
        $options['class'] = ($options['class'] ?? '') . ' ranees-avatar';
        return parent::user_picture($user, $options);
    }

    /**
     * Add a custom banner above the main content on the dashboard.
     */
    public function main_content() {
        global $PAGE;
        $output = '';
        if ($PAGE->pagetype === 'my-index') {
            $output .= '<div class="ranees-welcome-banner">';
            $output .= '<h2>' . get_string('welcome', 'theme_ranees') . '</h2>';
            $output .= '</div>';
        }
        $output .= parent::main_content();
        return $output;
    }
}""")

note("The class must be in the correct namespace (theme_ranees\\output) and the file must be at classes/output/core_renderer.php exactly. Moodle's autoloader finds it by convention.")
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 7. JAVASCRIPT — AMD MODULES
# ══════════════════════════════════════════════════════════════════════════
chapter("7. JavaScript — AMD Modules")

section("7.1 How Moodle's AMD System Works")
body("Moodle uses RequireJS with AMD (Asynchronous Module Definition). You write ES modules in <b>amd/src/</b> and they get compiled to <b>amd/build/</b> by Grunt. In development (with Theme Designer Mode on), Moodle can serve the source files directly.")

section("7.2 Creating a JS Module")
code("""// theme/ranees/amd/src/greeting.js

define(['jquery', 'core/log'], function($, log) {

    return {
        init: function(username) {
            log.debug('Ranees theme JS loaded for: ' + username);

            // Example: animate the welcome banner
            $('.ranees-welcome-banner').hide().fadeIn(600);

            // Example: custom dropdown behaviour
            $('.ranees-dropdown-toggle').on('click', function() {
                $(this).next('.ranees-dropdown-menu').toggleClass('show');
            });
        }
    };
});""")

section("7.3 Loading a Module from a Template")
body("Call your module from a Mustache template using the <b>data-module</b> pattern, or load it directly from a PHP layout file.")
code("""{{! In your Mustache template — pass data via JSON attribute }}
<div id="ranees-greeting"
     data-username="{{username}}"
     data-module="theme_ranees/greeting">
</div>""")
code("""// Or load from PHP layout file (layout/drawers.php)
$PAGE->requires->js_call_amd('theme_ranees/greeting', 'init', [$USER->firstname]);""")

section("7.4 Using Third-Party Libraries")
body("You have two options for adding third-party JS libraries:")
bullet("<b>Option A — Copy into amd/src/:</b> Place the library file in amd/src/ and require it as a local module. Good for versioning with your theme.")
bullet("<b>Option B — CDN via head.mustache:</b> Add a &lt;script&gt; tag in your head.mustache override. Simpler for libraries with CDN support.")
code("""{{! Option B — head.mustache: load Alpine.js from CDN }}
<head>
    {{> theme_boost/head}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>""")
note("For production, prefer Option A (self-hosted) so your site works offline and isn't dependent on external CDN availability.")
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 8. DEVELOPMENT WORKFLOW
# ══════════════════════════════════════════════════════════════════════════
chapter("8. Development Workflow (Docker Setup)")

section("8.1 Enable Theme Designer Mode")
body("Theme Designer Mode disables CSS/template caching so changes appear immediately without a manual purge. Enable it at:")
body("<b>Site administration → Appearance → Themes → Theme settings → Theme designer mode</b>")
note("Disable Theme Designer Mode on production — it significantly slows down page loads.")

section("8.2 Purging Caches")
body("After any change to SCSS, templates, or PHP files, you must purge Moodle's caches.")
code("""# Via CLI (fastest — use this during development)
docker exec moodle_app php /var/www/html/admin/cli/purge_caches.php

# Via browser
# Go to: http://localhost:8080/admin/purgecaches.php""")

section("8.3 Mounting Your Theme as a Docker Volume")
body("Instead of copying files into the container every time, mount your theme directory as a volume. This is the recommended development setup.")
code("""# docker-compose.yml — add a volume mount for your theme
services:
  moodle:
    volumes:
      - moodledata:/var/moodledata
      - ./theme/ranees:/var/www/html/theme/ranees  # ← add this line""")
body("Now any file you edit locally is instantly available inside the container. You only need to purge caches, not rebuild the image.")

section("8.4 Recommended Development Loop")
table([
    ["Action", "Command"],
    ["Edit SCSS or template", "Edit file locally (volume-mounted)"],
    ["Purge caches",    "docker exec moodle_app php /var/www/html/admin/cli/purge_caches.php"],
    ["Reload browser",  "Ctrl+Shift+R (hard reload to bypass browser cache)"],
    ["Check PHP errors","docker logs moodle_app"],
    ["Rebuild image",   "docker compose up -d --build (only after Dockerfile changes)"],
], col_widths=[5*cm, 11*cm])

section("8.5 Useful Admin URLs")
table([
    ["URL", "Purpose"],
    ["/admin/index.php",           "Site administration home"],
    ["/admin/purgecaches.php",     "Purge all Moodle caches"],
    ["/theme/index.php",           "Theme selector"],
    ["/admin/settings.php?section=themesettings", "Theme designer mode toggle"],
    ["/admin/settings.php?section=theme_ranees",  "Your theme's admin settings page"],
], col_widths=[7.5*cm, 8.5*cm])
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 9. COMMON MISTAKES
# ══════════════════════════════════════════════════════════════════════════
chapter("9. Common Mistakes")

mistakes = [
    ("Editing core theme files directly",
     "Any change inside theme/boost/ will be overwritten by the next Moodle upgrade. Always create a child theme and override only what you need."),
    ("Forgetting to purge caches",
     "Moodle aggressively caches SCSS, templates, and language strings. If your change isn't showing, run: php admin/cli/purge_caches.php"),
    ("Wrong theme name casing in config.php",
     "$THEME->name must exactly match the folder name, all lowercase. 'Ranees' will fail; 'ranees' will work."),
    ("Missing require_once in config.php",
     "config.php must start with: defined('MOODLE_INTERNAL') || die(); — without this, the file can be accessed directly."),
    ("SCSS syntax errors causing blank pages",
     "A single SCSS error causes the entire stylesheet to fail, resulting in an unstyled page. Check docker logs moodle_app for the PHP error message."),
    ("Wrong namespace in core_renderer.php",
     "The class must be in namespace theme_ranees\\output; and named core_renderer. Wrong namespace = Moodle won't find it."),
    ("Overriding templates without tracking upstream changes",
     "When you copy a template to override it, it no longer receives upstream fixes. After each Moodle upgrade, diff your overrides against the originals."),
    ("Using {{{triple braces}}} on untrusted data",
     "Triple braces output raw HTML without escaping. Only use it on data you fully control — never on user input."),
]

for title, desc in mistakes:
    section(f"✗  {title}")
    body(desc)
    spacer(0.1)

story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════
# 10. QUICK REFERENCE
# ══════════════════════════════════════════════════════════════════════════
chapter("10. Quick Reference")

section("Minimal config.php")
code("""<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name        = 'ranees';
$THEME->parents     = ['boost'];
$THEME->sheets      = [];
$THEME->extrascsscallback = 'theme_ranees_get_extra_scss';
$THEME->prescsscallback   = 'theme_ranees_get_pre_scss';
$THEME->scss_compiler_class = 'theme_boost\\\\autoprefixer';
$THEME->rendererfactory     = 'theme_overridden_renderer_factory';
$THEME->layouts = [/* copy from boost/config.php */];""")

section("Minimal lib.php")
code("""<?php
defined('MOODLE_INTERNAL') || die();

function theme_ranees_get_pre_scss($theme) {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/ranees/scss/pre.scss');
}

function theme_ranees_get_extra_scss($theme) {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/ranees/scss/post.scss');
}""")

section("Minimal version.php")
code("""<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2024042800;
$plugin->requires  = 2023042400;
$plugin->component = 'theme_ranees';""")

section("Minimal lang/en/theme_ranees.php")
code("""<?php
$string['pluginname'] = 'Ranees EdTech';
$string['choosereadme'] = 'Custom theme for Ranees EdTech platform.';""")

section("CLI Command Reference")
table([
    ["Command", "Purpose"],
    ["php admin/cli/purge_caches.php",                   "Purge all Moodle caches"],
    ["php admin/cli/cfg.php --name=theme --set=ranees",  "Set active theme via CLI"],
    ["php admin/cli/cfg.php --name=themedesignermode --set=1", "Enable designer mode"],
    ["php admin/cli/upgrade.php",                        "Run Moodle upgrade (after version bump)"],
    ["php admin/cli/install_database.php",               "Initial DB install (first run)"],
], col_widths=[9*cm, 7*cm])

spacer(0.5)
body("For questions and issues, refer to the official Moodle documentation at <b>docs.moodle.org</b> and the Moodle Developer documentation at <b>moodledev.io</b>.")

# ══════════════════════════════════════════════════════════════════════════
# BUILD
# ══════════════════════════════════════════════════════════════════════════
def header_footer(canvas, doc):
    if doc.page == 1:
        cover_page(canvas, doc)
        return
    canvas.saveState()
    # Header bar
    canvas.setFillColor(DARK)
    canvas.rect(0, H - 1.1*cm, W, 1.1*cm, fill=1, stroke=0)
    canvas.setFillColor(ORANGE)
    canvas.rect(0, H - 1.1*cm, 0.5*cm, 1.1*cm, fill=1, stroke=0)
    canvas.setFont("Helvetica-Bold", 9)
    canvas.setFillColor(WHITE)
    canvas.drawString(1.2*cm, H - 0.72*cm, "Moodle Theme Developer Guide")
    canvas.setFont("Helvetica", 9)
    canvas.drawRightString(W - 1.5*cm, H - 0.72*cm, "Ranees EdTech")
    # Footer
    canvas.setFillColor(LIGHT_BG)
    canvas.rect(0, 0, W, 0.9*cm, fill=1, stroke=0)
    canvas.setFillColor(ORANGE)
    canvas.rect(0, 0.9*cm, W, 0.12*cm, fill=1, stroke=0)
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(MUTED)
    canvas.drawString(2*cm, 0.32*cm, "Moodle 4.5  ·  Boost Child Theme  ·  SCSS + Mustache")
    canvas.drawRightString(W - 2*cm, 0.32*cm, f"Page {doc.page}")
    canvas.restoreState()

doc.build(story, onFirstPage=header_footer, onLaterPages=header_footer)
print(f"PDF saved to: {OUTPUT}")
