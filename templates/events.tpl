{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
  <link href="/css/events.css" rel="stylesheet" />
  <script src="/js/list.js"></script>
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <div id="center_block">
   {include file="event_list.tpl"}
  </div>
{/block}
