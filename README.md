This module will help you make your own templates for <title>, <description> and h1 on the pages of facet search.

This module is designed for use in Drupal 9, performance in Drupal 10 Not Checking.

To work the module facet Views must work without ajax. Views must have facet_pretty_path.

On the settings page, at https://yourwebsite.com/admin/config/search/taxonomy-facet-meta you need to set the url of your Facet Views PageThen you need to choose Pretty path coder type, that you select for your facets.  

After that select one or two taxonomy vocabulary that you use in facets.Next, past facet url alias, that you setting-up on you'r taxonomy facet.Next, you need to set up template for you tag. 

In template you need to use token - [token] as taxonomy name. For example - template "Articles from [token] category" on page with active facet category Recipe would be like "Articles from Recipe category". If you left field blank, your tag has default value.

If you will be working with two dictionaries, you can also expose tag templates for the situation where both facets are active.
