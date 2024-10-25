# The minimal Varnish version is 4.0
vcl 4.0;

# Include this subroutine into vcl_recv()
sub bot_detection {
    # Detect if bot
    # List from https://github.com/varnishcache/varnish-devicedetect/
    if (
        req.http.User-Agent ~ "(?i)(ads|google|bing|msn|yandex|baidu|ro|career|seznam|)bot" ||
        req.http.User-Agent ~ "(?i)(baidu|jike|symantec)spider" ||
        req.http.User-Agent ~ "(?i)(pingdom|facebookexternalhit|scanner|slurp|crawler)" ||
        # List from prerender suggestions https://github.com/prerender/prerender-node/blob/master/index.js
        req.http.User-Agent ~ "(?i)(yahoo|yandex|twitter|rogerbot|linkedinbot|embedly|quora link preview|showyoubot|outbrain|pinterest|developers.google.com\/+\/web\/snippet|slackbot|vkShare|W3C_Validator|redditbot|Applebot|WhatsApp|flipboard|tumblr|bitlybot|SkypeUriPreview|nuzzel|Discordbot|Google Page Speed|Qwantify|Bitrix link preview|XING-contenttabreceiver|TelegramBot)" ||
        # List from https://stackoverflow.com/a/48577189
        req.http.User-Agent ~ "(?i)(BotLink|bingbot|AhrefsBot|ahoy|AlkalineBOT|anthill|appie|arale|araneo|AraybOt|ariadne|arks|ATN_Worldwide|Atomz|bbot|Bjaaland|Ukonline|borg\-bot\/0\.9|boxseabot|bspider|calif|christcrawler|CMC\/0\.01|combine|confuzzledbot|CoolBot|cosmos|Internet Cruiser Robot|cusco|cyberspyder|cydralspider|desertrealm, desert realm|digger|DIIbot|grabber|downloadexpress|DragonBot|dwcp|ecollector|ebiness|elfinbot|esculapio|esther|fastcrawler|FDSE|FELIX IDE|ESI|fido|KIT\-Fireball|fouineur|Freecrawl|gammaSpider|gazz|gcreep|golem|googlebot|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|iajabot|INGRID\/0\.1|Informant|InfoSpiders|inspectorwww|irobot|Iron33|JBot|jcrawler|Teoma|Jeeves|jobo|image\.kapsi\.net|KDD\-Explorer|ko_yappo_robot|label\-grabber|larbin|legs|Linkidator|linkwalker|Lockon|logo_gif_crawler|marvin|mattie|mediafox|MerzScope|NEC\-MeshExplorer|MindCrawler|udmsearch|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|sharp\-info\-agent|WebMechanic|NetScoop|newscan\-online|ObjectsSearch|Occam|Orbsearch\/1\.0|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|Getterrobo\-Plus|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Search\-AU|searchprocess|Senrigan|Shagseeker|sift|SimBot|Site Valet|skymob|SLCrawler\/2\.0|slurp|ESI|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|nil|suke|http:\/\/www\.sygol\.com|tach_bw|TechBOT|templeton|titin|topiclink|UdmSearch|urlck|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|crawlpaper|wapspider|WebBandit\/1\.0|webcatcher|T\-H\-U\-N\-D\-E\-R\-S\-T\-O\-N\-E|WebMoose|webquest|webreaper|webs|webspider|WebWalker|wget|winona|whowhere|wlm|WOLP|WWWC|none|XGET|Nederland\.zoek|AISearchBot|woriobot|NetSeer|Nutch|YandexBot|YandexMobileBot|SemrushBot|FatBot|MJ12bot|DotBot|AddThis|baiduspider|SeznamBot|mod_pagespeed|CCBot|openstat.ru\/Bot|m2e)" ||
        # List below are bots required for the site features
        req.http.User-Agent ~ "ELB-HealthChecker" ||
        req.http.x-abuse-info ~ "New Relic" ||
        req.http.User-Agent ~ "Stripe"
    ) {
         set req.http.x-is-bot = "true";
    }
}
