<?xml version="1.0" encoding="UTF-8"?>
<!-- Browser view for /sitemap.xml. Search engines read the XML directly and ignore this file. -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="s xhtml">
<xsl:output method="html" encoding="UTF-8" indent="yes"/>
<xsl:template match="/">
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<meta name="robots" content="noindex"/>
<title>XML Sitemap - Gold Price Today Sri Lanka</title>
<style>
    :root { color-scheme: dark; }
    body { margin: 0; background: #08070a; color: #f7f1e3; font: 15px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
    .wrap { max-width: 1080px; margin: 0 auto; padding: 32px 16px 48px; }
    h1 { margin: 0 0 6px; font: 800 28px/1.2 Georgia, serif; color: #f6d675; }
    p { margin: 0 0 20px; color: #bfb3a0; }
    a { color: #f6d675; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .count { display: inline-block; padding: 3px 10px; border: 1px solid rgba(212,175,55,.4); border-radius: 999px; font-size: 13px; color: #f6d675; }
    .table { overflow-x: auto; border: 1px solid #2b2419; border-radius: 14px; background: #161310; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #2b2419; white-space: nowrap; }
    th { font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: #8c8072; background: #1e1911; }
    td:first-child { white-space: normal; word-break: break-all; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(212,175,55,.05); }
    .langs { font-size: 12px; color: #8c8072; }
</style>
</head>
<body>
<div class="wrap">
    <h1>XML Sitemap</h1>
    <p>
        This is the sitemap search engines use to find every page of
        <a href="/">Gold Price Today Sri Lanka</a>.
        <span class="count"><xsl:value-of select="count(s:urlset/s:url)"/> URLs</span>
    </p>
    <div class="table">
    <table>
        <thead><tr><th>URL</th><th>Last modified</th><th>Change frequency</th><th>Priority</th></tr></thead>
        <tbody>
        <xsl:for-each select="s:urlset/s:url">
            <tr>
                <td>
                    <a href="{s:loc}"><xsl:value-of select="s:loc"/></a>
                    <xsl:if test="xhtml:link">
                        <div class="langs">
                            <xsl:for-each select="xhtml:link[@hreflang != 'x-default']">
                                <xsl:value-of select="@hreflang"/>
                                <xsl:if test="position() != last()"> · </xsl:if>
                            </xsl:for-each>
                        </div>
                    </xsl:if>
                </td>
                <td><xsl:value-of select="s:lastmod"/></td>
                <td><xsl:value-of select="s:changefreq"/></td>
                <td><xsl:value-of select="s:priority"/></td>
            </tr>
        </xsl:for-each>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
