<?php /*
すべての layout について共通の、headセクションの記述
*/ ?>

<meta name="viewport" content="width=device-width" />
<meta name="keywords" content="<?= htmlspecialchars($px->site()->get_current_page_info('keywords')); ?>" />
<meta name="description" content="<?= htmlspecialchars($px->site()->get_current_page_info('description')); ?>" />

<?= $px->get_contents_manifesto(); ?>

<!-- Bootstrap -->
<link rel="stylesheet" href="<?= htmlspecialchars( $theme->files('/bootstrap/css/bootstrap.min.css') ); ?>">
<script src="<?= htmlspecialchars( $theme->files('/bootstrap/js/bootstrap.min.js') ); ?>"></script>

<!-- Bootstrap Theme -->
<link rel="stylesheet" href="<?= htmlspecialchars( $theme->files('/styles/bootstrap-theme.min.css') ); ?>">
<link rel="stylesheet" href="<?= htmlspecialchars( $theme->files('/styles/theme.css') ); ?>">
