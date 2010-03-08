<?php
/**
 * Widlflower Sitemap creation
 *
 */
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
 	  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php foreach ($pages as $page):?>
<?php if(isset($page['Page'])): ?>
    <url>
        <loc><?php echo FULL_BASE_URL.substr($this->webroot,0,strlen($this->webroot)-1).$page['Page']['url']; ?></loc>
        <lastmod><?php echo $time->toAtom($page['Page']['updated']); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php if(isset($pages['child'][$page['Page']['id']]) && !empty($pages['child'][$page['Page']['id']])): ?>
<?php foreach ($pages['child'][$page['Page']['id']] as $childPage):?>
    <url>
        <loc><?php echo FULL_BASE_URL.substr($this->webroot,0,strlen($this->webroot)-1).$childPage['Page']['url']; ?></loc>
        <lastmod><?php echo $time->toAtom($childPage['Page']['updated']); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
</urlset>
