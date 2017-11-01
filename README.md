# Klevu Smart Search for Magento 2
 Intelligent cloud hosted site search with self-learning search technology. Guaranteed to increase search-led conversions.<br /> 

<hr />
<h2>Compatible with</h2>
<li>CE 2.0.0 to 2.2.0</li>
<li>EE 2.0.0 to 2.2.0</li>

<hr />
<h2>Change Log</h2>
<h3>Version 2.1.24</h3>
<li>Multiple fixes</li>

<h3>Version 2.1.23</h3>
<li>Improvements on logging and configuration</li>
<li>Changes relating to warning messages for out of stock</li>
<li>Security improvements</li>
<li>Improvements on delete API requests</li>
 
<h3>Version 2.1.22</h3>
<li>Adding support for Magento 2.2</li>

<h3>Version 2.1.21</h3>
<li>Improvements on product stock data selection</li>

<h3>Version 2.1.20</h3>
<li>Sign up form improvments</li>
<li>list category and sku change</li>
<li>Set Updated date to "0000-00-00" in klevu sync table when tax,image,secure url value changed in klevu configuration</li>
<li>Some css and landing page improvements</li>

<h3>Version 2.1.19</h3>
<li>Tax calculation improvments</li>
<li>Code Improvements</li>
<li>Fix for cms pages in klevu search configuration</li>
<li>Sync products and Category per store views using the command line, check help center <a href="https://support.klevu.com/knowledgebase/sync-data-for-specific-store-view-using-command-line/">documentation</a> for usage. </li>

<h3>Version 2.1.18</h3>
<li>Commented stock function query</li>

<h3>Version 2.1.18</h3>
<li>Disable default magento ajax search call</li>
<li>Method change to collect stock information </li>
<li>Remove script tag from the content</li>
<li>Fix filter issue for iOS devices</li>
<li>Code improvements</li>
<li>Tax calculation improvments</li>
<li>Disable klevu cron from configuration</li>


<h3>Version 2.1.16</h3>

<li>We have made changes in the dependency parameters to meet requirements of your Magento version  2.0.*</li>

<h3>Version 2.1.15</h3>

<li>Options to enter image size from klevu search configuration</li>
<li>Notification if same api key used by two different stores</li>
<li>product sync bug fix for 2.0 EE and b2B </li>
<li>Trigger improvements</li>

<h3>Version 2.1.14</h3>

<li>Trigger based product updates</li>
<li>New Plans for klevu sign up</li>
<li>Bug fix for translatio</li>

<h3>Version 2.1.13</h3>
<li>bug fix: for css and category products stock conditions</li>

<h3>Version 2.1.12</h3>
<li>bug fix: corrected the namespace error</li>

<h3>Version 2.1.11</h3>
<li>Enabled filters on mobile by default</li>

<h3>Version 2.1.10</h3>
<li>Tested with Magento EQP and solved possible warnings and errors</li>
<li>Created indexing on klevu_product_sync table to optimize queries</li>
<li>Solved Cache invalidation message in debug.log of Magento which was caused by updating last sync date with every sync cycle</li>
<li>Removed the test mode from all code, queries and klevu_product_sync table</li>
<li>Collected Email Id when order is placed</li>
<li>Fixed a bug querying tables with prefix in DB</li>
<li>Encrypting email and session ids</li>
<li>Bug fix: Klevu template translation change</li>
<li>Removed a reference to garbage collection after every sync cycle</li>

<h3>Version 2.1.9</h3>
<li>Preserve layout improvements: Enabled search tracking, content section, conflict resolution.</li>
<li>Fixed category translation issue across multiple-sites.</li>
<li>Improved the SQL query for obtaining stock status.</li>
<li>Allowed multi-quantity with the add-to-cart option.</li>
<li>Added the order sync as part of the overall sync functionality when running via the command script.</li>

<h3>Version 2.1.8</h3>
<li>Bug fix for collecting price when using the collection method (now collecting the associated products' prices)</li>
<li>Using parent products' stock status for configurable products</li>
<li>Using parent products' images when the merchant has asked for the same in the Klevu configuration settings</li>
<li>Bug fix for notice error in order sync</li>
<li>Made it compatible with CE and EE 2.1.6</li>

<h3>Version 2.1.7</h3>
<li>Bug fix when upgrading from older version</li>
<li>Identifying if the pub folder is in use as a document root or not</li>
<li>Displaying the no result found message on the landing page with the preserve layout option</li>

<h3>Version 2.1.6</h3>
<li>Removed the website condition when obtaining stock status</li>
<li>Bug fixes for the preserve layout option</li>
<li>Security patches and javascript improvements</li>
<li>Removed default magento ajax search</li>
<li>Price bug fix when using the collection methos</li>
<li>M2's version specific changes for obtaining media gallery images' paths</li>

<h3>Version 2.1.1</h3>
<li>Hide certain category pages from search. </li>
<li>Send log to klevu server. </li>
<li>Image improvemets</li>
<li>Bug fix while inserting specific product in klevu_product_sync table</li>
<li>Bug fix for xml char issue while syncing data to klevu server</li>

<h3>Version 2.1.0</h3>
<li>Restructured the repository as per the M2 guidelines for uploading
    the package on the marketplace. </li>
<li>Please make sure to remove any older version (i.e. < 2.1.0) 
    before installing the current version. If you used composer method
    to install the plugin, use the following command to uninstall it. </li>
<li><b>composer remove klevu/module-search</b></li>
<li>Use the following command to install the plugin</li>
<li><b>composer require klevu/module-search</b></li>


<h3>Version 2.0.10</h3>
<li>Skip the product if config product does not have sub product;
    We're not catching the exception thrown by Magento in such cases.</li>
<li>Correct category names/urls are collected now.</li>
<li>Image path improvements to avoid invalid image URLs.</li>
<li>Preserve layout option for the Premium customers.</li>
<li>Collection method as an option now available.</li>

<h3>Version 2.0.9</h3>
<li>change page_id to row_id for CMS sync</li>

<h3>Version 2.0.8</h3>
<li>common package for enterprise and community edition</li>


<h3>Version 2.0.6</h3>
<li> Memory optimization for product sync </li>
<li> A bug fix for obtaining language code for frontend translations </li>

<h3>Version 2.0.5</h3>
<li> Removed the dependency error during the compile command </li>
<li> Bug fix for when Klevu_search module is installed prior to installing Magento_Catalog</li>
<li> Used Attribute code instead of the Attribute Name while installing Rating attribute </li>

<hr />
<h3><a href="http://support.klevu.com/knowledgebase/integration-steps-for-magento-2/" target="_blank"> Integration Steps</a></h3> 

<b>Note:</b>
Please make sure to remove any older version (i.e. < 2.1.0) before installing the current version. If you used composer method to install the plugin, use the following command to uninstall it.

composer remove klevu/module-search

Use the following command to install the plugin

composer require klevu/module-search

<b>Have questions? Need help?</b>  E-mail us at: <a href="mailto:support@klevu.com">support@klevu.com</a><br />
<hr />
<h2>Search that Learns, Generates Sales </h2>
Klevu’s Ajax-based search for Magento 2 goes  beyond fast response and auto-complete, to provide the most advanced search available today. Klevu helps deliver an awesome shopping experience for your customers directly from the search box. <br /><br />
<img src="http://www.klevu.com/img/img-slider/magento/magento-2-search.jpg" />

<h3>Klevu Search Features</h3>

<li>Self-learning search</li>
<li>Dynamic filters</li>
<li>Search results page</li>
<li>Fully customizable</li>
<li>Product promotions</li>
<li>100% cloud hosted, CDN backed and secure backups</li>
<li>Error tolerance: Automatically take care of spelling mistakes</li>
<li>Identifies stop words, inflections and more</li>
<li>Populates trending and popular searches</li>
<li>Synonym dictionary</li>
<li>URL redirect</li>
<li>Comprehensive data tracking & analysis</li>
<li>Easy integration process</li>
<br />


<b>Rich autocomplete</b><br />
Klevu automatically completes the search query by providing rich, insightful and intuitive suggestions. Autocomplete starts from 1st character!
<br /><br />

<b>Grid view layout</b><br />
Klevu provides ready made alternative to autocomplete layout. It is possible to show instant results with navigation panel in quick search.
<br /><br />

<b>Self-learning search</b><br />
Klevu continuously learns how shoppers interact by using search on the store. It builds on the learning and optimizes results. No manual work.
<br /><br />

<b>Fully customisable</b><br />
Klevu provides full control and flexibility on the look and feel of search results. Based on the open CSS principle, search results can be customized to any extent desired.
<br /><br />

<b>Error tolerance</b><br />
An enhanced keyword search index ensures that shoppers are always connected to the right products.
<br /><br />

<b>Stopwords</b><br />
Identifies and manages stop words (i.e. and, to, for…) to get to the heart of the search.
<br /><br />

<b>Inflections</b><br />
Understands what your shoppers mean even if they use verbs and adjectives in different grammatical forms.
<br /><br />

<b>Dynamic filters</b><br />
Automatically creates all relevant filters in the search results. Filters allows shoppers to explore results in great detail.
<br /><br />

<b>Trending searches</b><br />
Klevu prompts consumers with words from popular and recent searches, creating interaction with shoppers, and accelerating the buying process.
<br /><br />

<b>Automated catalog enrichment</b><br />
Klevu adds contextually relevant synonyms to your catalog in metadata format. This enrichment of your catalog data can result in 3x the depth and coverage of search results.
<br /><br />

<b>Synonym dictionary</b><br />
You can train the Klevu search robot for the unique elements related to your store by providing lists of keywords specific to your store. Klevu automatically identifies these words and delivers the best results.
<br /><br />

<b>URL redirect</b><br />
URL redirect allows you to direct shoppers to a certain page on specific keywords. For example, direct shoppers to a special promotion page when the keyword “holiday” is searched.
<br /><br />

<b>Product promotions</b><br />
Manual boosting of product ranking ensures that the products you want to promote appear at the top of the search results when relevant.
<br /><br />

<b>Analytics</b><br />
Klevu analytics help create razor sharp SEO and promotional campaigns. Concrete data to helps increase conversion rates. You’ll have complete visibility of the path from search to purchase.
<br />

<hr />

<h3>Have questions? Need help? Have feedback for us?</h3>
Check <a href="http://support.klevu.com/faq/faqs/"> FAQs  </a> section <b>OR</b> E-mail us at: <a href="mailto:support@klevu.com">support@klevu.com</a>
<br />
For more information please visit <a href="http://www.klevu.com/">http://www.klevu.com</a> 
<br />
Follow us on twitter <a href="https://twitter.com/klevusearch">@klevusearch</a> 
