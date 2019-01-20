---------------------------------------
MultiLangSetup
---------------------------------------
Version: 1.0.0-beta
Author: Murray <murray@digitalpenguin.hk>
---------------------------------------

MultiLangSetup
==============
A multi-language configuration Extra for MODX 2.

Please make sure you are running at least PHP 5.6 and preferably PHP 7.1+.

WARNING: Only install this package on a new blank MODX installation. Otherwise you WILL lose data in your home resource and BaseTemplate.

Once MultiLangSetup has been installed, it is recommended to uninstall and remove it from the package manager. All changes will have already been made during its installation.

Install Notes
-------------
There are two dependencies.

    - Babel (Optional: add the list of context keys to Babel's system settings.)
    - LangRouter (Optional: Set the default context in the system setting.)

    Note: MultiLang setup will attempt to overwrite their system settings during the process.

The installation process will set up the entire site to a working state ready to input language content and then launch.
The process is as follows:

    - Modify required general system settings:
        - friendly_urls = yes
        - use_alias_path = yes
    - Modify Babel and LangRouter system settings.
    - Rename ht.access to .htaccess for friendly URLs.
    - Create namespace
    - Create components directory according to the namespace
    - Create lexicons for supplied language keys
    - Replace content in BaseTemplate with a structure ready to go. (Include Babel language switcher and example lexicon tag)
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
    - Generate Babel links for each resource between contexts.