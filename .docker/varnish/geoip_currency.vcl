# The minimal Varnish version is 4.0
vcl 4.0;

# This is the file to correctly pass vrnish cache and set currency based on user country.
# This VCL script will work only together with Cloudflare location info: https://developers.cloudflare.com/support/network/configuring-ip-geolocation/

# Script should be included in main (default.vcl) script, in the beginning.
# After that in main VCL script call routins (functions) from current VCL.

 # GeoIP Currency
sub geoip_currency_recv {
    # The "X-Magento-GeoIP-Currency" header used for two things:
    #   1. Build a hash for varnish (check vcl_hash subroutine)
    #   2. If magento requires to update currency based on GeoIP - magento takes currency from this header

    # Do anything only if CloudFlare geolocation enabled
    if (req.http.CF-IPCountry) {
        # Bots always use AED currency (default)
        # This default currency must match magento default currency
        if (req.http.x-is-bot) {
            set req.http.X-Magento-GeoIP-Currency = "AED";
        } else if (!req.http.Cookie ~ "X-Magento-GeoIP-Currency") {
            # If cookie not exists - meaning need to trigger GeoIP currency set
            # Setting currency
            if (req.http.CF-IPCountry == "AE") {
                set req.http.X-Magento-GeoIP-Currency = "AED";
            } else {
                set req.http.X-Magento-GeoIP-Currency = "USD";
            }
            # This header tells magento to trigger update
            set req.http.X-Magento-GeoIP-Currency-Update = "1";
        } else {
            # If cookie exists, do not trigger update. But use value from cookie
            set req.http.X-Magento-GeoIP-Currency = regsub(req.http.Cookie,"(^|([^;]*;)+)\s*X-Magento-GeoIP-Currency=([^;]*)\s*($|(;[^;]*)+)","\3");
        }
    }
}

sub geoip_currency_hash {
    # Add GeoIP Currency to the hash
    if (req.http.X-Magento-GeoIP-Currency) {
        hash_data(req.http.X-Magento-GeoIP-Currency);
    }
}
