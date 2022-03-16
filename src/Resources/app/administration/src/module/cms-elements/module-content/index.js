import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement(
    {
        name: 'styla-module-content',
        label: 'styla-cms-integration-plugin.element.module-content.label',
        component: 'cms-element-styla-module-content',
        configComponent: 'cms-element-styla-module-content-config',
        previewComponent: 'cms-element-styla-module-content-preview',
        defaultConfig: {
            slotId: {
                source: 'static',
                value: '',
                required: true
            },
            enableCaching: {
                source: 'static',
                value: false
            }
        }
    }
);
