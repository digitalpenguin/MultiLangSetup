MultiLangSetup
==============
A multi-language configuration Extra for MODX 2.

Please make sure you are running at least PHP 5.6 and preferably PHP 7.1+.

Install Notes
-------------
There are two dependencies.


    - Babel (Optional: add the list of context keys to Babel's system settings.)
    - LangRouter (Optional: Set the default context in the system setting.)

The installation process will set up the entire site to a working state ready to input language content and then launch.
The process is as follows:

    - Check for Babel and LangRouter system settings.
    - Display the values in the setup options giving the user a chance to modify them.
    - Modify required general system settings:
        - friendly_urls = yes
        - use_alias_path = yes
        - site_name = Set from the setup options
    - Rename ht.access to .htaccess
    - Create namespace
    - Create lexicons for supplied language keys
    - Replace content in BaseTemplate with a structure ready to go. (Include language switcher and example lexicon tag)
    - Create contexts with keys corresponding to setup options.
    - On each context, create the following resources
        - Home
        - Page Not Found
        - Unauthorized
    - On each context, create the following context settings:
        - error_page
        - unauthorized_page
        - site_start
        - base_url
        - site_url
        - cultureKey
        - site_name
    - Using Babel, link appropriate resources on each page.
    
    
