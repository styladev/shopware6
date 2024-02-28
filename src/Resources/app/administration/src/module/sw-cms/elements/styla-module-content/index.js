import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement(
    {
        name: 'styla-module-content',
        label: 'styla-cms-integration-plugin.element.module-content.label',
        component: 'sw-cms-el-styla-module-content',
        configComponent: 'sw-cms-el-styla-module-content-config',
        previewComponent: 'sw-cms-el-styla-module-content-preview',
        defaultConfig: {
            slotId: {
                source: 'static',
                value: '',
                required: true
            }
        }
    }
);
