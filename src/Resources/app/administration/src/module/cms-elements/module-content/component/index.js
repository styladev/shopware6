import template from './cms-element-styla-module-content.html.twig';
import './styles.scss';

const { Component, Mixin } = Shopware;

Component.register('cms-element-styla-module-content', {
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
