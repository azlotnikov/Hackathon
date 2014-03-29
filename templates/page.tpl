{extends file='html.tpl'}
{block name='links' append}
	<link href="/css/main.css" rel="stylesheet" />
   <script src="/js/logout.js"></script>
{/block}
{block name='page'}
	<div id="wrap">
       {block name='div.main'}{/block}
	</div>
	{include file="footer.tpl"}
{/block}