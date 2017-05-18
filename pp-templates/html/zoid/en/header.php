<!DOCTYPE html>
<html lang="%lang%">
<head>
    <meta charset="UTF-8" />
    <title>%meta_title%</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="author" content="%pp_company%" />
    <meta name="description" content="%meta_desc%" />
    <meta name="generator" content="Zenbership Membership Software (www.zenbership.com)"/>
    <link href="%theme_url%/css/primary.css" rel="stylesheet" type="text/css" />
    <link href="%theme_url%/css/media.css" rel="stylesheet" type="text/css" />
</head>
<body class="zen">

<div id="zen_header" class="zen_edit_area" contenteditable="false">
    <div class="zen_holder">
        <div id="zen_head_left">
            <a href="%home_link%">
            <?php
            $logo = $this->get_option('company_logo');
            $company_name = $this->get_option('company_name');
            if (! empty($logo)) {
                echo "<img src=\"$logo\" class=\"home_logo\" alt=\"$company_name\" />";
            } else {
                echo $company_name;
            }
            ?>
            </a>
        </div>
        <?php
        $session = new session;
        $ses = $session->check_session();
        if ($ses['error'] != '1') {
            ?>
            {-site_topbar_logged_in-}
            <?php
        } else {
            ?>
            {-site_topbar-}
            <?php
        }
        ?>
        <div id="navExpand"><img src="%theme_url%/imgs/icon-menu-black.png" width="24" height="24" alt="Menu" /></div>
        <ul id="mobileMenu"></ul>
    </div>
</div>

<div id="zen_overall_body" class="zen_holder zen_edit_area">
    <div id="zen_primary_content">

        %error_code%
        %success_code%
        <div id="zen_section_header" class="zen_fonts zen_shadow_light" contenteditable="false">
            <p id="zen_breadcrumbs" class="zen_small">%pp_breadcrumbs%</p>
            <h1 id="zen_main_heading" class="zen_shadow_light">%page_title%</h1>
        </div>