const hyvaTailwindPreset = require('./vendor/hyva-themes/magento2-default-theme/web/tailwind/tailwind.config.js');
const { screens } = require('tailwindcss/defaultTheme');

hyvaTailwindPreset.plugins = [];

const path = require('path')
const fs = require('fs')

/**
 * Finds and lists all files in a directory with a specific extension
 * https://gist.github.com/victorsollozzo/4134793
 * @return Array
 */
function recFindByExt(base,ext, files,result) {
    files = files || fs.readdirSync(base)
    result = result || []

    files.forEach(
        function (file) {
            const newbase = path.join(base,file);
            if (fs.statSync(newbase).isDirectory()) {
                result = recFindByExt(newbase, ext, fs.readdirSync(newbase), result)
            } else {
                if (file.substr(-1*(ext.length+1)) == '.' + ext) {
                    result.push(newbase)
                }
            }
        }
    )
    return result
}

/**
 * Returns an array of all files to be used in tailwind purge.content
 */
const purgeContent = () => {
    // Add any sub-directories you wish to be excluded by Tailwind when checking
    // the hyva-default theme.
    // For example you may have removed Magento_Review from your store, and therefore
    // do not wish for Tailwind to generate any CSS for it.
    const EXCLUDE_FROM_PARENT = [
        'Magento_Review',
        'Magento_Bundle',
        'Magento_Cookie',
        'Magento_Downloadable',
        'Magento_GiftMessage',
        'Magento_LoginAsCustomerFrontendUi',
        'Magento_Paypal',
        'Magento_ProductAlert',
        'Magento_SendFriend',
        'Magento_Vault',
    ]; // e.g. ['Magento_Review']

    // Declare array to stores all paths for hyvaDefault theme's phtml files
    let hyvaDefault = recFindByExt('vendor/hyva-themes/magento2-default-theme/', 'phtml');

    // Declare array to stores all paths for your current theme's phtml files
    let currentTheme = [
        ...recFindByExt('app/code/Magebit/','phtml'),
        ...recFindByExt('app/code/Magebit/','xml'),
        ...recFindByExt('app/design/frontend/Magebit/bizaar/','phtml'),
        ...recFindByExt('app/design/frontend/Magebit/bizaar/','xml'),
    ];

    // Filter the array of templates from hyva-default to remove any templates overridden in your theme.
    // A similar filter can be used on other parent theme's if you have a
    // multi-store Magento install using a different theme structure.
    hyvaDefault = hyvaDefault.filter(function(item) {
        let isAllowed = true;

        for (const key in this) {
            if (item.includes(this[key].replace(/../g, ''))) {
                isAllowed = false;
            }
        }

        return isAllowed;
    }, currentTheme.concat(EXCLUDE_FROM_PARENT));

    return currentTheme.concat(hyvaDefault);
}

module.exports = {
    presets: [
        hyvaTailwindPreset
    ],
    mode: 'jit',
    theme: {
        screens: {
            'xxs-max': {'max': '369px'}, // @media (max-width: 369px)
            'xss-max': {'max': '395px'}, // @media (max-width: 395px)
            'xs-max': {'max': '479px'}, // @media (max-width: 479px)
            'xs': '480px', // @media (min-width: 480px)

            'ss': '530px', // @media (min-width: 530px)
            'ss-only': {'min': '530px', 'max': '639px'}, // @media (min-width: 530px and max-width: 639px)
            'ss-lg': {'min': '530px', 'max': '1023px'}, // @media (min-width: 530px and max-width: 1023px)

            'sm-max': {'max': '639px'},  // @media (max-width: 639px)
            'sm-only': {'min': '640px', 'max': '767px'}, // @media (min-width: 640px and max-width: 767px)
            'sm': screens['sm'], // tailwind sm screen

            'md-max': {'max': '767px'}, // @media (max-width: 767px)
            'md-only': {'min': '768px', 'max': '1023px'}, // @media (min-width: 640px and max-width: 767px)
            'md': screens['md'], // tailwind md screen
            'md-px': '769px', // @media (min-width: 769px)
            'md-xl': {'min': '767px', 'max': '1280px'}, // @media (min-width: 767px and max-width: 1280px)

            'lg-max': {'max': '1023px'}, // // @media (max-width: 1023px)
            'lg': screens['lg'], // tailwind lg screen,

            'xl-max': {'max': '1279px'}, // // @media (max-width: 1279px)
            'xl': screens['xl'], // tailwind xl screen,

            '2xl-max': {'max': '1535px'}, // // @media (max-width: 1535px)
            '2xl': screens['2xl'], // tailwind 2xl screen,

            '3xl-max': {'max': '1649px'}, // @media (max-width: 1650px)
            '3xl':  {'min': '1650px'}, // @media (min-width: 1650px),

            'small-desktop-only': {'min': '1024px', 'max': '1650px'}, // @media (min-width: 640px and max-width: 767px)
        },
        extend: {
            fontFamily: {
                'sans': ['mont', 'sans-serif'],
                'icomoon': ['icomoon']
            },
            fontSize: {
                '2xs': ['10px', {lineHeight: '16px'}],
                'xs': ['12px', {lineHeight: '20px'}],
                'sm': ['14px', {lineHeight: '24px'}],   // H5 & Body (mobile)
                'base': ['16px', {lineHeight: '28px'}], // Body & H5 & H4 (mobile)
                'md': ['18px', {lineHeight: '28px'}],   // H4
                'lg': ['20px', {lineHeight: '28px'}],   // H3 (mobile)
                'xl': ['24px', {lineHeight: '32px'}],   // H3 & H2 (mobile)
                '2xl': ['28px', {lineHeight: '36px'}],  // H1 (mobile)
                '3xl': ['32px', {lineHeight: '44px'}],  // H2
                '4xl': ['36px', {lineHeight: '44px'}],  // H1
                '5xl': ['40px', {lineHeight: '52px'}]
            },
            colors: {
                green: {
                    DEFAULT: '#00D7B2',
                    'darker': '#00A387'
                },
                blue: {
                    DEFAULT: '#0165FF',
                    'darker': '#0151CC'
                },
                navy: {
                    DEFAULT: '#062B48',
                    'darker': '#05253D'
                },
                gray: {
                    DEFAULT: '#505F6B',
                    'fa': '#FAFBFC',
                    'f2': '#F2F5F7',
                    'e6': '#E6E9EB',
                    'e1': '#E1E4E5',
                    'd4': '#D4DBE0'
                },
                black: {
                    DEFAULT: '#000000'
                },
                success: '#0AD443',
                error: '#E11313',
                warning: '#DCB900',
                inherit: 'inherit',
                currentColor: 'currentColor'
            },
            spacing: {
                // When converting from px multiply rem by 4 and use as key (must be divisible by 5)
                '0.25': '0.0625rem', // 1px
                '0.5': '0.125rem', // 2px
                '0.75': '0.1875rem', // 3px
                '1.25': '0.313rem', // 5px
                '1.5': '0.375rem', // 6px
                '1.75': '0.4375rem', // 7px
                '2.25': '0.563rem', // 9px
                '2.5': '0.625rem', // 10px
                '2.75': '0.688rem', // 11px
                '3.25': '0.813rem', // 13px
                '3.5': '0.875rem', // 14px
                '3.75': '0.9375rem', // 15px
                '4.25': '1.063rem', // 17px
                '4.5': '1.125rem', // 18px
                '4.75': '1.188rem', // 19px
                '5.25': '1.313rem', // 21px
                '5.5': '1.375rem', // 22px
                '5.75': '1.4375rem', // 23px
                '6.25': '1.5625rem', // 25px
                '6.5': '1.625rem', // 26px
                '6.75': '1.688rem', // 27px
                '7.25': '1.813rem', // 29px
                '7.5': '1.875rem', // 30px
                '7.75': '1.938rem', // 31px
                '8.25': '2.063rem', // 33px
                '8.5': '2.125rem', // 34px
                '8.75': '2.188rem', // 35px
                '9.5': '2.375rem', // 38px
                '9.75': '2.4375rem', // 39px
                '10.25': '2.563rem', // 41px
                '10.5': '2.625rem', // 42px
                '10.75': '2.6875em', // 43px
                '11.25': '2.8125rem', // 45px
                '11.5': '2.875rem', // 46px
                '11.75': '2.9375rem', // 47px
                '12.25': '3.063rem', // 49px
                '12.5': '3.125rem', // 50px
                '13': '3.25rem', // 52px
                '14.5': '3.625rem', // 58px
                '15': '3.75rem', // 60px
                '19': '4.75rem', // 76px
                '21': '5.25rem', // 84px
                '25': '6.25rem', // 100px
                '30': '7.5rem', //120px
                '34.25': '8.563rem', // 137px
                '37': '9.25rem', //148px
            },
            width: {
                'min': 'min-content',
                'max': 'max-content',
                'fit': 'fit-content',
                'unset': 'unset',
                'full-px': 'calc(100% + 1px)',
                'search-pop': 'calc(100% + 80px)',
                'search-pop-content': 'calc(100% - 80px)'
            },
            minWidth: {
                'fit': 'fit-content',
                '4': '1rem', // 14px
                '12': '3rem', // 48px
                '13': '3.25rem', // 52px
                '36.5': '9.125rem' // 146px
            },
            maxWidth: {
                'unset': 'unset',
                '1/2': '50%'
            },
            height: {
                'min': 'min-content',
                'max': 'max-content',
                'fit': 'fit-content',
                'menu': 'calc(100vh - 176px)',
                'menu-main': 'calc(100vh - 283px)',
                'menu-sidebar': 'calc(100vh - 316px)',
                'search-pop-container': 'calc(100vh - 120px)'
            },
            maxHeight: {
                'dialog': 'calc(100vh - 64px)',
                '60.5': '15.125rem', // 242px
                'search-pop': 'calc(100vh - 160px)'
            },
            minHeight: {
                'inherit': 'inherit'
            },
            borderRadius: {
                DEFAULT: '5px'
            },
            borderColor: {
                DEFAULT: '#E6E9EB'
            },
            boxShadow: {
                'header': '0 40px 40px -35px rgba(6, 43, 72, 0.1)',
                'item': '0 0 40px rgb(6 43 72 / 13%)',
                'item-xl': '0 0 50px rgb(6 43 72 / 23%)',
                'search-pop': '0px 4px 40px rgb(6 43 72 / 13%)',
                'blur': '0 0 5px currentColor'
            },
            outline: {
                'gray-e6': '1px solid #E6E9EB',
                'white': '1px solid #FFFFFF',
                'blue-2': ['2px solid #0165FF', '2px']
            },
            gridTemplateColumns: {
                'menu': '366px 1fr',
                'pdp': '32.292% 40.756% 23.829%', // PDP info
                'qty-atc': '132px 1fr',
                'qty-atc-mobile': '35.822% 1fr',
                'qty-atc-fixed': '138px 1fr'
            },
            zIndex: {
                '60': 60
            },
        }
    },
    corePlugins: {
        container: false
    },
    plugins: [
        function ({ addComponents }) {
            addComponents({
                '.container': {
                    maxWidth: '100%',
                    paddingLeft: 'theme(spacing[5])',
                    paddingRight: 'theme(spacing[5])',
                    marginLeft: 'auto',
                    marginRight: 'auto',
                    '@screen md': {
                        paddingLeft: 'theme(spacing[8])',
                        paddingRight: 'theme(spacing[8])',
                    },
                    '@screen 2xl': {
                        maxWidth: '1600px'
                    }
                }
            })
        }
    ],
    purge: {
        enabled: true,
        safelist: [
            'sticky',
            'top-0',
            'z-40',
            'bg-success',
            'bg-error',
            'bg-warning',
            'rounded-br-none',
            'rounded-bl-none',
            'lg:p-12',
            'lg:px-60'
        ],
        content: purgeContent()
    }
}
