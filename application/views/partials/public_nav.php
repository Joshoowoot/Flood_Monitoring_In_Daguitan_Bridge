<?php defined('BASEPATH') OR exit('No direct script access allowed');
$home_url = isset($home_url) ? $home_url : site_url();
$announcements_url = isset($announcements_url) ? $announcements_url : site_url('announcements');
$about_url = isset($about_url) ? $about_url : site_url('about');
$nav_page = isset($nav_page) ? $nav_page : 'home';
$home_active = ($nav_page === 'home');
$ann_active = ($nav_page === 'announcements');
$about_active = ($nav_page === 'about');
?>
<a href="<?php echo html_escape($home_url); ?>"<?php echo $home_active ? ' class="is-current"' : ''; ?>>Home</a>
<a href="<?php echo html_escape($announcements_url); ?>"<?php echo $ann_active ? ' class="is-current"' : ''; ?>>Announcements</a>
<a href="<?php echo html_escape($about_url); ?>"<?php echo $about_active ? ' class="is-current"' : ''; ?>>About</a>
