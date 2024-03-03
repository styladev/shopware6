import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock(
    {
        name: 'styla-module-content',
        label: 'styla-cms-integration-plugin.blocks.module-content.label',
        category: 'text',
        component: 'sw-cms-block-styla-module-content',
        previewComponent: 'sw-cms-preview-styla-module-content',
        defaultConfig: {},
        slots: {
            content: {
                type: 'styla-module-content'
            }
        }
    }
);
