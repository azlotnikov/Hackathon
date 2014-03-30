<header>
  <ul class="style_menu top_menu">
    {if $isLogin}
    <li>
      <a href="/profile" data="profile">Личный кабинет</a>
    </li><li id="logout">
      <a href="#">Выход</a>
    </li>
    {else}
    <li>
      <a href="/login" data="login">Вход</a>
    </li><li>
      <a href="/registration" data="registration">Регистрация</a>
    </li>
    {/if}
  </ul>
  <a href='/'><img src="/img/logo.png" class="logo" /></a>
  <nav>
    <ul class="style_menu">
      <li><a href="/" data="main">Главная</a></li><li><a href="/map" data="main">Карта</a></li><li><a href="/" data="main">Все события</a></li>
    </ul>
  </nav>
</header>
<script type="text/javascript">
  $('header a[data="{$active_item|default:'main'}"]').addClass('active');
</script>
