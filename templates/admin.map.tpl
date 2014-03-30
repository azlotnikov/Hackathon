{extends file='html.tpl'}
{block name='title' append} - Админ-панель{/block}
{block name='links' append}
  <link href="/css/admin.css" rel="stylesheet" />
  <link href="/css/main.css" rel="stylesheet" />
  <script src="/js/admin.js"></script>
{/block}
{block name='page'}
<div id="wrap">
  <header>
    <nav>
      <ul>
        <li><a href="/admin/map">Карта</a></li>
        <li><a href="/admin/change_pass">Сменить пароль</a></li>
        <li><a href="/admin/logout">Выход</a></li>
      </ul>
    </nav>
  </header>
  <div id="top_block">
  </div>
</div>
{/block}
