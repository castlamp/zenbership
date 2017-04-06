<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>%meta_title%</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
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
        <div id="navExpand"><img src="%theme_url%/imgs/icon-menu.png" width="24" height="24" alt="Menu" /></div>
        <ul id="mobileMenu"></ul>
    </div>
</div>

<div class="zen_holder zen_edit_area">
    %error_code%
    %success_code%
    <div id="zen_section_header" class="zen_fonts zen_shadow_light" contenteditable="false">
        <p id="zen_breadcrumbs" class="zen_small">%pp_breadcrumbs%</p>
        <h1 class="zen_shadow_light">%page_title%</h1>
    </div>