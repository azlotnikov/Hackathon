{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/index.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
  <script src="/js/list.js"></script>
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  {include file="event_list.tpl"}
{/block}
