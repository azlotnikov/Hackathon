<?php
@session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';

AuthorizedUser::Unauthorized();
