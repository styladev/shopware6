(this.webpackJsonp=this.webpackJsonp||[]).push([["styla-cms-integration-plugin"],{"4PXt":function(e,t){e.exports='{% block styla_plugin_settings_icon %}\n    <sw-icon name="styla-plugin-settings"></sw-icon>\n{% endblock %}\n'},"4RnA":function(e,t,n){"use strict";n.r(t);var a=n("FZBp"),i=n.n(a);Shopware.Component.register("cms-block-styla-module-content",{template:i.a});var s=n("e+K4"),o=n.n(s);n("dniG");Shopware.Component.register("cms-block-styla-module-content-preview",{template:o.a}),Shopware.Service("cmsService").registerCmsBlock({name:"styla-module-content",category:"text",label:"styla-cms-integration-plugin.blocks.module-content.label",component:"cms-block-styla-module-content",previewComponent:"cms-block-styla-module-content-preview",defaultConfig:{},slots:{content:{type:"styla-module-content"}}});var l=n("BLav"),c=n.n(l),r=(n("aSPI"),Shopware),u=r.Component,g=r.Mixin;u.register("cms-element-styla-module-content",{template:c.a,mixins:[g.getByName("cms-element")],created:function(){this.createdComponent()},methods:{createdComponent:function(){this.initElementConfig("styla-module-content")}}});var d=n("xh6E"),p=n.n(d),h=Shopware,m=h.Component,f=h.Mixin;m.register("cms-element-styla-module-content-config",{template:p.a,mixins:[f.getByName("cms-element")],created:function(){this.createdComponent()},methods:{createdComponent:function(){this.initElementConfig("styla-module-content")}}});var y=n("TlAQ"),S=n.n(y);n("zapo");Shopware.Component.register("cms-element-styla-module-content-preview",{template:S.a}),Shopware.Service("cmsService").registerCmsElement({name:"styla-module-content",label:"styla-cms-integration-plugin.element.module-content.label",component:"cms-element-styla-module-content",configComponent:"cms-element-styla-module-content-config",previewComponent:"cms-element-styla-module-content-preview",defaultConfig:{slotId:{source:"static",value:"",required:!0}}});var v=n("GLYz"),_=n.n(v),C=(n("u29L"),Shopware),b=C.Component,w=C.Defaults,k=Shopware.Data.Criteria;b.register("styla-cms-integration-settings",{template:_.a,inject:["repositoryFactory"],data:function(){return{isLoading:!1,isSaveSuccessful:!1,defaultAccountNameFilled:!1,messageAccountBlankErrorState:null,config:null,salesChannels:[]}},metaInfo:function(){return{title:this.$createTitle()}},created:function(){this.createdComponent()},computed:{salesChannelRepository:function(){return this.repositoryFactory.create("sales_channel")},defaultAccountNameErrorState:function(){return this.defaultAccountNameFilled?null:this.messageAccountBlankErrorState},hasError:function(){return!this.defaultAccountNameFilled}},watch:{config:{handler:function(){var e=this.$refs.configComponent.allConfigs.null,t=this.$refs.configComponent.selectedSalesChannelId;this.defaultAccountNameFilled=null===t?!!this.config["StylaCmsIntegration.settings.defaultAccountName"]:!!this.config["StylaCmsIntegration.settings.defaultAccountName"]||!!e["StylaCmsIntegration.settings.defaultAccountName"]},deep:!0}},methods:{createdComponent:function(){var e=this;this.isLoading=!0;var t=new k;t.addFilter(k.equalsAny("typeId",[w.storefrontSalesChannelTypeId,w.apiSalesChannelTypeId])),this.salesChannelRepository.search(t,Shopware.Context.api).then((function(t){t.add({id:null,translated:{name:e.$tc("sw-sales-channel-switch.labelDefaultOption")}}),e.salesChannels=t})).finally((function(){e.isLoading=!1})),this.messageAccountBlankErrorState={code:1,detail:this.$tc("styla-cms-integration-plugin.configuration.field.accountNames.default.error.empty")}},onSave:function(){this.hasError||this.save()},save:function(){var e=this;this.isLoading=!0,this.$refs.configComponent.save().then((function(){e.isSaveSuccessful=!0})).finally((function(){e.isLoading=!1}))}}});var I=n("r5uX"),x=n.n(I),A=Shopware,z=A.Component,P=A.Mixin,N=Shopware.Data.Criteria,L=Shopware.Utils.format.date;z.register("styla-cms-integration-page-listing",{template:x.a,inject:["repositoryFactory","stylaPageApiService","stylaSynchronizationApiService","systemConfigApiService"],mixins:[P.getByName("notification")],data:function(){return{repository:null,pages:null,lastSuccessSynchronizationDate:null,scheduleSynchronizationSuccess:!1,scheduleSynchronizationProcessing:!1}},metaInfo:function(){return{title:this.$createTitle()}},computed:{columns:function(){return this.getColumns()}},created:function(){this.createdComponent()},methods:{createdComponent:function(){var e=this;this.repository=this.repositoryFactory.create("styla_cms_page"),this.domainsRepository=this.repositoryFactory.create("sales_channel_domain");var t=new N;t.addSorting(N.sort("createdAt","ASC")),t.addFilter(N.equals("salesChannel.typeId","8a243080f92e4c719546314b577cf82b"));var n=this.domainsRepository.search(t,Shopware.Context.api);n.then((function(t){e.domainsList=t}));var a=this.systemConfigApiService.getValues("StylaCmsIntegrationPlugin");a.then((function(t){e.settings=t}));var i=null,s=this.repository.search(new N,Shopware.Context.api);s.then((function(e){i=e})),Promise.all([s,a,n]).then((function(){e.pages=i})),this.stylaSynchronizationApiService.getLastSuccessSynchronizationDate().then((function(t){t.data.result?e.lastSuccessSynchronizationDate=L(t.data.result,{hour:"2-digit",minute:"2-digit"}):e.lastSuccessSynchronizationDate=e.$tc("styla-cms-integration-plugin.actions.get-last-success-date.not_available")})).catch((function(t){console.error("Failed to get last success synchronization date",t),e.createNotificationError({message:e.$tc("styla-cms-integration-plugin.actions.get-last-success-date.message.failed")})}))},getColumns:function(){return[{property:"title",label:this.$tc("styla-cms-integration-plugin.page.listing.grid.column.title"),allowResize:!0,primary:!0},{property:"path",label:this.$tc("styla-cms-integration-plugin.page.listing.grid.column.path"),allowResize:!0},{property:"accountName",label:this.$tc("styla-cms-integration-plugin.page.listing.grid.column.account-name"),allowResize:!0},{property:"createdAt",label:this.$tc("styla-cms-integration-plugin.page.listing.grid.column.created-at"),allowResize:!0}]},scheduleSynchronization:function(){this.stylaPageApiService.scheduleSynchronization().then(function(e){e.data.isScheduled?this.createNotificationSuccess({message:this.$tc("styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.success")}):"SYNCHRONIZATION_IS_ALREADY_RUNNING"===e.data.errorCode?this.createNotificationWarning({message:this.$tc("styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.is-running")}):this.createNotificationWarning({message:this.$tc("styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.was-not-scheduled")})}.bind(this)).catch(function(e){void 0!==e.response.data.errorCode&&console.error("Failed to schedule styla pages synchronization, error code: "+e.response.data.errorCode),this.createNotificationError({message:this.$tc("styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.failed")})}.bind(this))},refreshPageDetails:function(e){var t=this.stylaPageApiService.refreshPageDetails(e.id);this.createNotificationInfo({message:this.$tc("styla-cms-integration-plugin.actions.refresh-page-details.message.scheduled")}),t.then(function(e){e.data.isSuccess?this.createNotificationSuccess({message:this.$tc("styla-cms-integration-plugin.actions.refresh-page-details.message.success")}):"PAGE_NOT_FOUND"===e.data.errorCode?this.createNotificationWarning({message:this.$tc("styla-cms-integration-plugin.actions.refresh-page-details.message.page-not-found")}):this.createNotificationError({message:this.$tc("styla-cms-integration-plugin.actions.refresh-page-details.failed")})}.bind(this)).catch(function(e){void 0!==e.response.data.errorCode&&console.error("Failed to refresh styla page details, error code: "+e.response.data.errorCode),this.createNotificationError({message:this.$tc("styla-cms-integration-plugin.actions.refresh-page-details.message.failed")})}.bind(this))},computePathLink:function(e){if(0===this.domainsList.length)return null;if(this.pagesPathHashMap&&this.pagesPathHashMap[e.id])return this.pagesPathHashMap[e.id];var t=null,n=this.settings["StylaCmsIntegrationPlugin.config.accountNames"];for(var a in n)if(e.accountName===n[a]){t=a;break}var i=null;t&&"default"!==t&&(i=this.domainsList.find((function(e){return e.languageId===t}))),i||(i=this.domainsList[0]);var s=""+e.path,o=i.url.replace(/\/$/,"")+"/"+s.replace(/^\//,"");return this.pagesPathHashMap||(this.pagesPathHashMap={}),this.pagesPathHashMap[e.id]=o,o},resetScheduleSynchronizationState:function(){this.scheduleSynchronizationSuccess=!1,this.scheduleSynchronizationProcessing=!1}}});var T=n("4PXt"),$=n.n(T);Shopware.Component.register("styla-plugin-settings-icon",{template:$.a});var D=n("CmLL"),F=n.n(D),E=Shopware.Component,M=Shopware.Data.Criteria;E.register("styla-cms-integration-settings-accounts",{template:F.a,inject:["repositoryFactory"],props:{actualConfigData:{type:Object,required:!0},allConfigs:{type:Object,required:!0},selectedSalesChannelId:{type:String,required:!1,default:null},defaultAccountNameErrorState:{type:Object,required:!1,default:null},defaultAccountNameFilled:{type:Boolean,required:!0}},data:function(){return{configPath:"StylaCmsIntegration.settings.accountNames",isLoading:!1,systemLanguages:[]}},created:function(){this.createdComponent()},computed:{languageRepository:function(){return this.repositoryFactory.create("language")},accountNames:{get:function(){return this.allConfigs[this.selectedSalesChannelId]["StylaCmsIntegration.settings.accountNames"]}}},methods:{createdComponent:function(){var e=this;this.isLoading=!0;var t=new M;t.addSorting(M.sort("createdAt","ASC")),this.languageRepository.search(t,Shopware.Context.api).then((function(t){e.systemLanguages=t,e.initLanguageConfig()})).finally((function(){e.isLoading=!1}))},initLanguageConfig:function(){void 0===this.allConfigs[this.selectedSalesChannelId][this.configPath]&&this.$set(this.allConfigs[this.selectedSalesChannelId],"StylaCmsIntegration.settings.accountNames",{})},checkTextFieldInheritance:function(e){return"string"!=typeof e||e.length<=0}}});var O=n("WA+w"),j=n.n(O);function R(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=e&&("undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"]);if(null==n)return;var a,i,s=[],o=!0,l=!1;try{for(n=n.call(e);!(o=(a=n.next()).done)&&(s.push(a.value),!t||s.length!==t);o=!0);}catch(e){l=!0,i=e}finally{try{o||null==n.return||n.return()}finally{if(l)throw i}}return s}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return B(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return B(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function B(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}Shopware.Component.register("styla-cms-integration-settings-general",{template:j.a,props:{actualConfigData:{type:Object,required:!0},allConfigs:{type:Object,required:!0},selectedSalesChannelId:{type:String,required:!1,default:null}},created:function(){this.createdComponent()},methods:{createdComponent:function(){for(var e="StylaCmsIntegration.settings.",t=0,n=Object.entries({pagesListSynchronizationInterval:10,pageCacheDuration:3600});t<n.length;t++){var a=R(n[t],2),i=a[0],s=a[1];void 0===this.allConfigs.null[e+i]&&this.$set(this.allConfigs.null,e+i,s)}},checkTextFieldInheritance:function(e){return"string"!=typeof e||e.length<=0}}});var V=n("AETE");Shopware.Module.register("styla-cms-integration",{type:"plugin",name:"styla-cms-integration",title:"styla-cms-integration-plugin.configuration.label",description:"styla-cms-integration-plugin.page.listing.description",color:"#ffd53d",icon:"small-default-stack-line2",snippets:{"en-GB":V},routes:{settings:{component:"styla-cms-integration-settings",path:"settings",meta:{parentPath:"sw.settings.index.plugins"}},"pages-list":{component:"styla-cms-integration-page-listing",path:"styla/page"}},navigation:[{id:"styla-cms-integration-general",label:"styla-cms-integration-plugin.menu.title",color:"#ffd53d",icon:"small-default-stack-line2",parent:"sw-content",position:100},{label:"styla-cms-integration-plugin.page.listing.title",color:"#77ff3d",icon:"small-default-stack-line2",path:"styla.cms.integration.pages-list",parent:"styla-cms-integration-general",position:100}],settingsItem:{group:"plugins",to:"styla.cms.integration.settings",iconComponent:"styla-plugin-settings-icon",backgroundEnabled:!0}});var q,H=(q=n("e/iJ")).keys().reduce((function(e,t){var n={name:t.split(".")[1].split("/")[1],functional:!0,render:function(e,n){var a=n.data;return e("span",{class:[a.staticClass,a.class],style:a.style,attrs:a.attrs,on:a.on,domProps:{innerHTML:q(t)}})}};return e.push(n),e}),[]),Z=Shopware.Component;H.map((function(e){return Z.register(e.name,e)}));function G(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}var J=function(){function e(t,n){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.httpClient=t,this.loginService=n,this.name="stylaPageApiService"}var t,n,a;return t=e,(n=[{key:"scheduleSynchronization",value:function(){var e=this.getHeaders();return this.httpClient.post("/styla/page/_action/schedule-pages-synchronization",{},{headers:e})}},{key:"refreshPageDetails",value:function(e){var t=this.getHeaders();return this.httpClient.post("/styla/page/_action/refresh-details/"+e,{},{headers:t})}},{key:"getHeaders",value:function(){return{Accept:"application/json",Authorization:"Bearer ".concat(this.loginService.getToken()),"Content-Type":"application/json"}}}])&&G(t.prototype,n),a&&G(t,a),e}();function U(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}Shopware.Service().register("stylaPageApiService",(function(e){var t=Shopware.Application.getContainer("init");return new J(t.httpClient,e.loginService)}));var Y=function(){function e(t,n){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.httpClient=t,this.loginService=n,this.name="stylaPageApiService"}var t,n,a;return t=e,(n=[{key:"getLastSuccessSynchronizationDate",value:function(){var e=this.getHeaders();return this.httpClient.get("/styla/synchronization/page/_action/get_last_success_date_time",{},{headers:e})}},{key:"getHeaders",value:function(){return{Accept:"application/json",Authorization:"Bearer ".concat(this.loginService.getToken()),"Content-Type":"application/json"}}}])&&U(t.prototype,n),a&&U(t,a),e}();Shopware.Service().register("stylaSynchronizationApiService",(function(e){var t=Shopware.Application.getContainer("init");return new Y(t.httpClient,e.loginService)}))},AETE:function(e){e.exports=JSON.parse('{"styla-cms-integration-plugin":{"configuration":{"label":"Styla CMS Integration","header":"Styla CMS Integration plugin Settings","group":{"general":{"title":"General Settings"},"accounts":{"title":"Account Settings"}},"field":{"accountNames":{"default":{"label":"Default account name","error":{"empty":"Default account name is required"}},"language_specific_account_name":{"label":"{name} account name"}},"extraPages":{"label":"List of Shopware paths the plugin can override","helpText":"The plugin per default can only take over the home page and category pages\' paths.\\nIf you publish pages with matching paths (blank path for the home page) in Styla Editor then the plugin will display the Styla content on these Shopware paths.\\nIf you want to achieve this for other Shopware paths (like a PDP) then you need to add it on the list below, each one in a new row.\\nExamples:\\n/foo-example-path/\\n/foo-example-path/bar-example-path/"},"syncInterval":{"label":"Interval of the pages list synchronization (in minutes)","helpText":"This setting defines how often the plugin checks Styla API for page updates. You can see these updates in the Content > Styla CMS > Styla Pages tab. The lower the value the more Shopware resources will be used. The minimum value is 1 minute. The default is 10 minutes."},"pageCache":{"label":"Page details cache lifetime (in seconds)","helpText":"This setting defines for how long Shopware caches Styla page content. The lower the value the more Shopware resources will be used. The minimum value is 1 second. The default is 3600 seconds (one hour)."}}},"blocks":{"module-content":{"label":"Styla Modular Content"}},"element":{"module-content":{"text":"Styla Modular Content, id: \\"{id}\\"","label":"Styla Modular Content","configuration":{"slot-id":{"label":"Slot Id (assigned at https://editor.styla.com/)"}}}},"page":{"listing":{"title":"Styla Pages","description":"List of Styla Pages","grid":{"column":{"title":"Page title","path":"Page path","account-name":"Account name","created-at":"Created at"}}}},"actions":{"schedule-pages-synchronization":{"button":{"label":"Schedule pages synchronization"},"message":{"success":"Pages synchronization scheduled","failed":"Failed to schedule pages synchronization","is-running":"Pages synchronization was not scheduled because another synchronization is already running","was-not-scheduled":"Pages synchronization was not scheduled"}},"refresh-page-details":{"button":{"label":"Refresh page details"},"message":{"success":"Page details refreshed","failed":"Failed to refresh page details","page-not-found":"Page with such name was not found. Please schedule pages synchronization (button above)","scheduled":"Page details refresh scheduled"}},"get-last-success-date":{"not_available":"N/A","text":"Last successful synchronization: \\"{dateTime}\\"","message":{"failed":"Failed to fetch last successful synchronization date"}}},"menu":{"title":"Styla CMS"}}}')},BLav:function(e,t){e.exports="<div class=\"cms-element-styla-module-content\">\n    <h2>\n        {{ $tc('styla-cms-integration-plugin.element.module-content.text', 0, {'id': element.config.slotId.value}) }}\n    </h2>\n</div>\n"},COz6:function(e,t,n){},CmLL:function(e,t){e.exports='{% block styla_cmsintegration_content_card_channel_config_accounts_card %}\n    <sw-card class="sw-card--grid"\n             :title="$tc(\'styla-cms-integration-plugin.configuration.group.accounts.title\')">\n\n        {% block styla_cmsintegration_content_card_channel_config_accounts_card_container %}\n            <sw-container>\n\n                {% block styla_cmsintegration_content_card_channel_config_accounts_card_container_settings %}\n                    <div v-if="actualConfigData" class="card-container">\n\n                        {% block styla_cmsintegration_content_card_channel_config_accounts_card_container_settings_default %}\n                            <sw-inherit-wrapper v-model="actualConfigData[\'StylaCmsIntegration.settings.defaultAccountName\']"\n                                                :inheritedValue="selectedSalesChannelId === null ? null : allConfigs[\'null\'][\'StylaCmsIntegration.settings.defaultAccountName\']"\n                                                :customInheritationCheckFunction="checkTextFieldInheritance">\n                                <template #content="props">\n                                    <sw-text-field name="StylaCmsIntegration.settings.defaultAccountName"\n                                                   :mapInheritance="props"\n                                                   :label="$tc(\'styla-cms-integration-plugin.configuration.field.accountNames.default.label\')"\n                                                   :disabled="props.isInherited"\n                                                   :value="props.currentValue"\n                                                   :error="defaultAccountNameErrorState"\n                                                   :required="true"\n                                                   @change="props.updateCurrentValue">\n                                    </sw-text-field>\n                                </template>\n                            </sw-inherit-wrapper>\n                        {% endblock %}\n\n                        {% block styla_cmsintegration_content_card_channel_config_accounts_card_container_settings_language_accounts %}\n                            <template v-for="language in systemLanguages">\n\n                                <sw-inherit-wrapper v-model="accountNames[language.id]"\n                                                    :inheritedValue="selectedSalesChannelId === null ? null : allConfigs[\'null\'][\'StylaCmsIntegration.settings.accountNames\'][language.id]"\n                                                    :customInheritationCheckFunction="checkTextFieldInheritance">\n                                    <template #content="props">\n                                        <sw-text-field :mapInheritance="props"\n                                                       :label="$tc(\'styla-cms-integration-plugin.configuration.field.accountNames.language_specific_account_name.label\', 0, { name: language.name })"\n                                                       :disabled="props.isInherited"\n                                                       :value="props.currentValue"\n                                                       @change="props.updateCurrentValue">\n                                        </sw-text-field>\n                                    </template>\n                                </sw-inherit-wrapper>\n\n                            </template>\n                        {% endblock %}\n\n                    </div>\n                {% endblock %}\n            </sw-container>\n        {% endblock %}\n\n        {% block styla_cmsintegration_content_card_channel_config_accounts_card_loading %}\n            <sw-loader v-if="isLoading"></sw-loader>\n        {% endblock %}\n\n    </sw-card>\n{% endblock %}\n'},FZBp:function(e,t){e.exports='<div class="cms-block-styla-module-content">\n    <slot name="content">\n    </slot>\n</div>\n'},GLYz:function(e,t){e.exports='{% block styla_cmsintegration %}\n    <sw-page class="styla-cmsintegration-settings">\n        {% block styla_cmsintegration_header %}\n            <template #smart-bar-header>\n                <h2>\n                    {{ $tc(\'sw-settings.index.title\') }}\n                    <sw-icon name="small-arrow-medium-right" small></sw-icon>\n                    {{ $tc(\'styla-cms-integration-plugin.configuration.header\') }}\n                </h2>\n            </template>\n        {% endblock %}\n        {% block styla_cmsintegration_actions %}\n            <template #smart-bar-actions>\n                {% block styla_cmsintegration_actions_save %}\n                    <sw-button-process v-model="isSaveSuccessful"\n                                       class="sw-settings-login-registration__save-action"\n                                       variant="primary"\n                                       :isLoading="isLoading"\n                                       :disabled="isLoading"\n                                       @click="onSave">\n                        {{ $tc(\'global.default.save\') }}\n                    </sw-button-process>\n                {% endblock %}\n            </template>\n        {% endblock %}\n        {% block styla_cmsintegration_content %}\n            <template #content>\n                {% block styla_cmsintegration_content_card %}\n                    <sw-card-view>\n                        {% block styla_cmsintegration_content_card_channel_config %}\n                            <sw-sales-channel-config v-model="config"\n                                                     ref="configComponent"\n                                                     domain="StylaCmsIntegration.settings">\n                                {% block styla_cmsintegration_content_card_channel_config_sales_channel %}\n                                    <template #select="{ onInput, selectedSalesChannelId }">\n                                        {% block styla_cmsintegration_content_card_channel_config_sales_channel_card %}\n                                            <sw-card class="sw-card--grid"\n                                                     :title="$tc(\'global.entities.sales_channel\', 2)">\n                                                <div class="card-container">\n                                                    {% block styla_cmsintegration_content_card_channel_config_sales_channel_card_title %}\n                                                        <sw-single-select v-model="selectedSalesChannelId"\n                                                                          labelProperty="translated.name"\n                                                                          valueProperty="id"\n                                                                          :isLoading="isLoading"\n                                                                          :options="salesChannels"\n                                                                          @change="onInput">\n                                                        </sw-single-select>\n                                                    {% endblock %}\n                                                </div>\n                                            </sw-card>\n                                        {% endblock %}\n                                    </template>\n                                {% endblock %}\n                                {% block styla_cmsintegration_content_card_channel_config_cards %}\n                                    <template #content="{ actualConfigData, allConfigs, selectedSalesChannelId }">\n                                        <div v-if="actualConfigData">\n                                            {% block styla_cmsintegration_content_card_channel_config_accounts %}\n                                                <styla-cms-integration-settings-accounts\n                                                    :actualConfigData="actualConfigData"\n                                                    :allConfigs="allConfigs"\n                                                    :selectedSalesChannelId="selectedSalesChannelId"\n                                                    :defaultAccountNameErrorState="defaultAccountNameErrorState"\n                                                    :defaultAccountNameFilled="defaultAccountNameFilled">\n                                                </styla-cms-integration-settings-accounts>\n                                            {% endblock %}\n                                            {% block styla_cmsintegration_content_card_channel_config_general %}\n                                                <styla-cms-integration-settings-general\n                                                    :actualConfigData="actualConfigData"\n                                                    :allConfigs="allConfigs"\n                                                    :selectedSalesChannelId="selectedSalesChannelId">\n                                                </styla-cms-integration-settings-general>\n                                            {% endblock %}\n                                        </div>\n                                    </template>\n                                {% endblock %}\n                            </sw-sales-channel-config>\n                        {% endblock %}\n                        {% block styla_cmsintegration_content_card_loading %}\n                            <sw-loader v-if="isLoading"></sw-loader>\n                        {% endblock %}\n                    </sw-card-view>\n                {% endblock %}\n            </template>\n        {% endblock %}\n    </sw-page>\n{% endblock %}\n'},TlAQ:function(e,t){e.exports='<div class="cms-element-styla-module-content-preview">\n    <img class="cms-element-styla-module-content-preview-image" :src="\'stylacmsintegrationplugin/administration/static/img/shopware-6-styla-module.png\' | asset"/>\n</div>\n'},"WA+w":function(e,t){e.exports='{% block styla_cmsintegration_content_card_channel_config_accounts_card %}\n    <sw-card class="sw-card--grid"\n             :title="$tc(\'styla-cms-integration-plugin.configuration.group.general.title\')">\n\n        {% block styla_cmsintegration_content_card_channel_config_accounts_card_container %}\n            <sw-container>\n\n                {% block styla_cmsintegration_content_card_channel_config_accounts_card_container_settings %}\n                    <div v-if="actualConfigData" class="card-container">\n\n                        {% block styla_cmsintegration_content_card_channel_config_pages %}\n                            <sw-inherit-wrapper v-model="actualConfigData[\'StylaCmsIntegration.settings.extraPagesAllowedToOverride\']"\n                                                :inheritedValue="selectedSalesChannelId === null ? null : allConfigs[\'null\'][\'StylaCmsIntegration.settings.extraPagesAllowedToOverride\']"\n                                                :customInheritationCheckFunction="checkTextFieldInheritance">\n                                <template #content="props">\n                                    <sw-textarea-field name="StylaCmsIntegration.settings.extraPagesAllowedToOverride"\n                                                       :mapInheritance="props"\n                                                       :label="$tc(\'styla-cms-integration-plugin.configuration.field.extraPages.label\')"\n                                                       :helpText="$tc(\'styla-cms-integration-plugin.configuration.field.extraPages.helpText\')"\n                                                       :disabled="props.isInherited"\n                                                       :value="props.currentValue"\n                                                       @change="props.updateCurrentValue">\n                                    </sw-textarea-field>\n                                </template>\n                            </sw-inherit-wrapper>\n                        {% endblock %}\n                        {% block styla_cmsintegration_content_card_channel_config_interval %}\n                            <sw-inherit-wrapper v-model="actualConfigData[\'StylaCmsIntegration.settings.pagesListSynchronizationInterval\']"\n                                                :inheritedValue="selectedSalesChannelId === null ? null : allConfigs[\'null\'][\'StylaCmsIntegration.settings.pagesListSynchronizationInterval\']">\n                                <template #content="props">\n                                    <sw-field name="StylaCmsIntegration.settings.pagesListSynchronizationInterval"\n                                              type="number"\n                                              number-type="int"\n                                              :min="1"\n                                              :mapInheritance="props"\n                                              :label="$tc(\'styla-cms-integration-plugin.configuration.field.syncInterval.label\')"\n                                              :helpText="$tc(\'styla-cms-integration-plugin.configuration.field.syncInterval.helpText\')"\n                                              :disabled="props.isInherited"\n                                              :value="props.currentValue"\n                                              :required="true"\n                                              @change="props.updateCurrentValue">\n                                    </sw-field>\n                                </template>\n                            </sw-inherit-wrapper>\n                        {% endblock %}\n                        {% block styla_cmsintegration_content_card_channel_config_cache_lifetime %}\n                            <sw-inherit-wrapper v-model="actualConfigData[\'StylaCmsIntegration.settings.pageCacheDuration\']"\n                                                :inheritedValue="selectedSalesChannelId === null ? null : allConfigs[\'null\'][\'StylaCmsIntegration.settings.pageCacheDuration\']">\n                                <template #content="props">\n                                    <sw-field name="StylaCmsIntegration.settings.pageCacheDuration"\n                                              type="number"\n                                              number-type="int"\n                                              :min="1"\n                                              :mapInheritance="props"\n                                              :label="$tc(\'styla-cms-integration-plugin.configuration.field.pageCache.label\')"\n                                              :helpText="$tc(\'styla-cms-integration-plugin.configuration.field.pageCache.helpText\')"\n                                              :disabled="props.isInherited"\n                                              :value="props.currentValue"\n                                              :required="true"\n                                              @change="props.updateCurrentValue">\n                                    </sw-field>\n                                </template>\n                            </sw-inherit-wrapper>\n                        {% endblock %}\n\n                    </div>\n                {% endblock %}\n            </sw-container>\n        {% endblock %}\n\n    </sw-card>\n{% endblock %}\n'},Z8dF:function(e,t,n){},aSPI:function(e,t,n){var a=n("Z8dF");"string"==typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);(0,n("SZ7m").default)("12502387",a,!0,{})},dniG:function(e,t,n){var a=n("COz6");"string"==typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);(0,n("SZ7m").default)("dccf75ba",a,!0,{})},"e+K4":function(e,t){e.exports='<div>\n    <img class="cms-block-styla-module-content-preview-image" :src="\'stylacmsintegrationplugin/administration/static/img/shopware-6-styla-module.png\' | asset"/>\n</div>\n'},"e/iJ":function(e,t,n){var a={"./icons-styla-plugin-settings.svg":"uCzA"};function i(e){var t=s(e);return n(t)}function s(e){if(!n.o(a,e)){var t=new Error("Cannot find module '"+e+"'");throw t.code="MODULE_NOT_FOUND",t}return a[e]}i.keys=function(){return Object.keys(a)},i.resolve=s,e.exports=i,i.id="e/iJ"},e6R8:function(e,t,n){},ibkk:function(e,t,n){},r5uX:function(e,t){e.exports='{% block styla_pages_listing %}\n    <sw-page class="overdose-contact-list">\n        <template #smart-bar-actions> {# slots check vue.js documentation #}\n            <div v-if="lastSuccessSynchronizationDate" style="margin-top: 10px">\n                <sw-label variant="info" :dismissable="false">\n                    {{ $tc(\'styla-cms-integration-plugin.actions.get-last-success-date.text\', 0, {\'dateTime\': lastSuccessSynchronizationDate}) }}\n                </sw-label>\n            </div>\n            <sw-button-process variant="ghost"\n                               :isLoading="scheduleSynchronizationProcessing"\n                               :processSuccess="scheduleSynchronizationSuccess"\n                               @process-finish="resetScheduleSynchronizationState"\n                               @click="scheduleSynchronization"\n            >\n                {{ $tc(\'styla-cms-integration-plugin.actions.schedule-pages-synchronization.button.label\') }}\n            </sw-button-process>\n        </template>\n        <template #content>\n            <sw-entity-listing\n                v-if="pages"\n                :items="pages"\n                :repository="repository"\n                :showSelection="false"\n                :allowColumnEdit="false"\n                :allowDelete="false"\n                :allowEdit="false"\n                :columns="columns">\n                <template #actions="{ item }">\n                    <sw-context-menu-item @click="refreshPageDetails(item)">\n                        {{ $tc(\'styla-cms-integration-plugin.actions.refresh-page-details.button.label\') }}\n                    </sw-context-menu-item>\n                </template>\n\n                <template v-slot:column-path="{item, column, url}">\n                    <a v-bind:href="computePathLink(item)" v-if="computePathLink(item)" target="_blank"> {{ computePathLink(item) }} </a>\n                    <span v-else>{{ item.path }}</span>\n                </template>\n            </sw-entity-listing>\n        </template>\n    </sw-page>\n{% endblock %}\n'},u29L:function(e,t,n){var a=n("e6R8");"string"==typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);(0,n("SZ7m").default)("bf91e692",a,!0,{})},uCzA:function(e,t){e.exports='<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="84.000000pt" height="84.000000pt" viewBox="0 0 84.000000 84.000000" preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,84.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M280 690 l0 -30 190 0 190 0 0 30 0 30 -190 0 -190 0 0 -30z"></path><path d="M137 573 c-4 -3 -7 -19 -7 -35 l0 -28 180 0 180 0 0 35 0 35 -173 0 c-96 0 -177 -3 -180 -7z"></path><path d="M228 430 l-5 -30 194 0 193 0 0 30 0 30 -189 0 -189 0 -4 -30z"></path><path d="M340 300 l0 -30 180 0 180 0 0 30 0 30 -180 0 -180 0 0 -30z"></path><path d="M180 155 l0 -35 195 0 195 0 0 35 0 35 -195 0 -195 0 0 -35z"></path></g></svg>'},xh6E:function(e,t){e.exports='<sw-field v-model="element.config.slotId.value"\n          :label="$tc(\'styla-cms-integration-plugin.element.module-content.configuration.slot-id.label\')">\n</sw-field>\n'},zapo:function(e,t,n){var a=n("ibkk");"string"==typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);(0,n("SZ7m").default)("038175dc",a,!0,{})}},[["4RnA","runtime","vendors-node"]]]);