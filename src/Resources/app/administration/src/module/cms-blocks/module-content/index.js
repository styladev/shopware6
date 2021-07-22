import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock(
    {
        name: 'styla-module-content',
        category: 'text',
        label: 'styla-cms-integration-plugin.blocks.module-content.label',
        component: 'cms-block-styla-module-content',
        previewComponent: 'cms-block-styla-module-content-preview',
        defaultConfig: {},
        slots: {
            content: {
                type: 'styla-module-content'
            }
        }
    }
);
