import template from './sw-cms-el-styla-module-content.html.twig';
import './styles.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-styla-module-content', {
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
