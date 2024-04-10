{**
 * plugins/generic/iiifViewer/templates/display.tpl
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Embedded viewing of a Bits XML file.
 *}

{include file="frontend/components/header.tpl" pageTitle="BitsXMLviewer"}

<script src="{$pluginUrl}/libs/bitsviewer.js"></script>



<div class="grid-container" style="display: grid; grid-template-columns: 150px 600px;">
<div class="grid-item" style="
    /*grid-column: 1 / span 1;*/
    border-right-color: black;
    border-right-width: thin;
    border-right-style: dashed;">

   <!-- Left menu Tab links -->
   <div class="bv-lm-tab">
      <button class="bv-lm-tablinks" onclick="openBVMenu(event, 'BVIndex')">Index</button>
      <button class="bv-lm-tablinks" onclick="openBVMenu(event, 'BVReferences')">References</button>
      <button class="bv-lm-tablinks" onclick="openBVMenu(event, 'BVResources')">Resources</button>
   </div>



	{capture assign="submissionUrl"}{url op="book" path=$publishedSubmission->getBestId()}{/capture}

   <!-- Left Menu Tab Content -->

   <div id="BVIndex" class="bv-tab-content">

	<h3>Book {$domNode->nodeName}</h3>

   </div>

   <div id="BVReferences" class="bv-tab-content">

	<h3>References</h3>

   </div>

   <div id="BVResources" class="bv-tab-content">

	<h3>Resources</h3>


{foreach name="nodeListLoop" from=$domNode->childNodes item=childnode}

	{assign var="node_name" value=$childnode->nodeName}

<p>
{if $node_name != '#text'}
got node {$node_name}
{/if}
</p>
   {if $node_name == 'book-meta'}
<h3>got book-meta node: {$node_name}</h3>
      {foreach name="bookmetaLoop" from=$childnode->childNodes item=metanode}
         {assign var="meta_node_name" value=$metanode->nodeName}
{if $meta_node_name != '#text'}
<h3>got  {$meta_node_name}</h3>
{/if}
         {if $meta_node_name == 'book-title-group'}

            {foreach name="tgLoop" from=$metanode->childNodes item=tgnode}
               {assign var="tg_node_name" value=$tgnode->nodeName}
{if $tg_node_name != '#text'}
<h3>got tg node {$tg_node_name}</h3>
{/if}
               {if $tg_node_name == 'book-title'}
                  {assign var="book_title" value=$tgnode->nodeValue}
               {/if}
               {if $tg_node_name == 'subtitle'}
                  {assign var="sub_title" value=$tgnode->nodeValue}
               {/if}
            {/foreach}
         {/if}

         {if $meta_node_name == 'isbn'}
            {assign var="isbn" value=$metanode->nodeValue}
         {/if}

      {/foreach}

   {/if}
{/foreach}

   </div>
</div>

<div class="grid-item" style="

    /*grid-column: 2 / span 3;*/
">

                  <h2>{$book_title}</h2>
                  <h3>{$sub_title}</h3>
                  <h4>{$isbn}</h4>

</div>


</div>
{include file="frontend/components/footer.tpl"}
