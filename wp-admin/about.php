<?php


$version = '1.1';
?>
<!DOCTYPE html>
<html>
<head>
    <title>About CosmicWord</title>
    <style>
        .wrap { max-width: 800px; margin: 20px auto; padding: 20px; }
        .nav-tab-wrapper { border-bottom: 1px solid #ccc; margin: 0; padding-top: 9px; }
        .nav-tab { display: inline-block; padding: 5px 10px; text-decoration: none; color: #555; }
        .nav-tab-active { border: 1px solid #ccc; border-bottom-color: #fff; background: #fff; }
        .about__section { margin: 2em 0; padding: 1em; background: #fff; border: 1px solid #ddd; }
        .privacy-notice { background: #fff8e5; padding: 1em; border-left: 4px solid #ffb900; }
        .gpl-notice { background: #f8f9f9; padding: 1em; border: 1px solid #ddd; }
        .has-2-columns { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2em; }
        .is-vertically-aligned-center { align-self: center; }
        .about__image { text-align: center; }
        .about__header-title { text-align: center; margin-bottom: 2em; }
        hr.is-large { margin: 2em 0; border: none; border-top: 1px solid #ddd; }
        img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class="wrap about__container">
        <div class="about__header">
            <div class="about__header-title">
                <h1>CosmicWord <?php echo htmlspecialchars($version); ?></h1>
            </div>
        </div>

      
        <div class="about__section">
            <h2>Welcome to CosmicWord <?php echo htmlspecialchars($version); ?></h2>
            <p class="is-subheading">Experience enhanced security and a robust plugin ecosystem with CosmicWord 1.1. Our built-in SEO tools are lightweight yet powerful, ensuring your site ranks well without compromising performance.</p>
        </div>

        <div class="about__section changelog">
            <div class="column">
                <h2>Built-in Light-Weight SEO</h2>
                <p>Version 1.1 introduced 10 SEO enhancements. For more information, see <a href="https://cosmicword.org/docs/seo-enhancements">the SEO documentation</a>.</p>
            </div>
        </div>

        <div class="has-2-columns">
            <div class="about__section">
                <h3>Additional Security Features</h3>
                <p><strong>Advanced security protocols to protect your site.</strong> CosmicWord 1.1 comes with enhanced security measures that go beyond standard offerings, ensuring your website remains secure against potential threats.</p>
            </div>
            <div class="about__section">
                <h3>CosmicWord Plugin Repository</h3>
                <p><strong>Access to thousands of plugins through our proprietary repository.</strong> CosmicWord utilizes its own plugin repository, offering all your favorite plugins and tens of thousands more you haven't discovered yet.</p>
            </div>
        </div>

        <div class="about__section">
            <h3>Performance Optimizations</h3>
            <p><strong>Optimized for speed and efficiency.</strong> CosmicWord 1.1 is more secure out of the box and performs better than its predecessors, ensuring your site loads faster and operates smoothly.</p>
        </div>

        <hr class="is-large">

        <div class="about__section privacy-notice">
            <h3>Important Privacy Notice</h3>
            <p>As this is a fork of WordPress, you should be aware that WordPress.org, forkedplugin.com and cosmicword.com  collects data about themes, plugins, and core software usage, including:</p>
            <ul>
                <li>Version checks for updates</li>
                <li>Usage tracking of installed themes and plugins</li>
                <li>Environment information when debugging is enabled</li>
            </ul>
            <p><strong>What This Means For You:</strong> When using themes or plugins from WordPress.org or cosmicword.com or forkedplugin.com, here on out referred to as "servers" or when your site checks for updates from the servers, your site may communicate with the servers.  This data sent to the servers may include personally identifiable information, including but not limited to, what you type in the search box, what you download, your IP address, and your website.</p>
        </div>

        <div class="about__section gpl-notice">
            <h3>License and Freedom</h3>
            <p>CosmicWord code is released under the <a href="https://www.gnu.org/licenses/gpl-2.0.html">GNU General Public License version 2</a> or (at your option) any later version. As a fork of WordPress, we maintain full compliance with the GPL to respect both the letter and spirit of free software.</p>
            <p>This means you are free to:</p>
            <ul>
                <li>Run the program for any purpose</li>
                <li>Study how the program works and modify it</li>
                <li>Redistribute copies</li>
                <li>Distribute modified versions</li>
            </ul>
            <p>All modifications and improvements to the original WordPress code in version 1.1 of CosmicWord are  available under the same license.</p>
        </div>

        <div class="about__section">
            <h3>WordPress Compatibility</h3>
            <p>While we've made improvements and modifications to the core software, to our knowledge we are comaptible  with WordPress themes and plugins where possible. Note that some features may still attempt to connect to WordPress.org, specifically the theme section still connects to their site. We would like that to change in a future version.</p>
        </div>

        <hr class="is-large">

        <div class="return-to-dashboard">
            <a href="index.php">Go to Dashboard</a> | 
        </div>
    </div>
</body>
</html>