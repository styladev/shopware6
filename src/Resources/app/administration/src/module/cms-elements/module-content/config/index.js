import template from './cms-element-styla-module-content-config.html.twig';

const { Component, Mixin } = Shopware;

Component.register('cms-element-styla-module-content-config', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('styla-module-content');
        }
    }
});
