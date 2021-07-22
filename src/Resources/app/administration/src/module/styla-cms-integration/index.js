import enGB from './snippet/en-GB.json';

import './pages/page-listing';

const { Module } = Shopware;
Module.register(
    'styla-cms-integration',
    {
        'type': 'plugin',
        'name': 'styla-cms-integration',
        'title': 'styla-cms-integration-plugin.page.listing.title',
        'description': 'styla-cms-integration-plugin.page.listing.description',
        'color': '#ffd53d',
        'icon': 'small-default-stack-line2',
        snippets: {
            'en-GB': enGB,
        },

        routes: {
            "pages-list": {
                component: "styla-cms-integration-page-listing",
                path: "styla/page"
            }
        },

        navigation: [
            {
                id: 'styla-cms-integration-general', // Id will be used to link navigation menu hierarchy
                label: 'styla-cms-integration-plugin.menu.title', // The name of the node menu point
                color: '#ffd53d',
                icon: 'small-default-stack-line2',
                parent: 'sw-content',
                position: 100
            },
            {
                label: 'styla-cms-integration-plugin.page.listing.title',
                color: '#77ff3d',
                icon: 'small-default-stack-line2',
                path: 'styla.cms.integration.pages-list',
                parent: 'styla-cms-integration-general',
                position: 100
            },
        ]
    }
);
