<header>
  <a href='/'><img src="/images/logo.png" class="logo" /></a>
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
  <nav>
    <ul class="style_menu">
      <li><a href="/" data="main">Главная</a></li><li><a href="/about" data="about">О проекте</a></li><li><a href="/photographs" data="photographs">Фотографы</a></li><li><a href="/videographs" data="videographs">Видеографы</a></li><li><a href="/photosessions" data="photosessions">Фотосессии</a></li><li><a href="/videosessions" data="videosessions">Видеосессии</a></li>
  </ul>
  </nav>
</header>
<script type="text/javascript">
  $('header a[data="{$active_item|default:'main'}"]').addClass('active');
</script>
