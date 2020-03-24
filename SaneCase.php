<?php
class SaneCase {
	public static function onBeforeDisplayNoArticleText($article) {
		$title = $article->getTitle();

		$db = wfGetDB(DB_MASTER);
		$r_page = $db->selectRow('page', ['page_id'], [
			'page_namespace' => $title->mNamespace,
			'convert(page_title using utf8mb4)' => $title->mDbkeyform,
		]);

		if ($r_page) {
			$r_title = Title::newFromID($r_page->page_id);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $r_title->getLocalURL());
		}
	}
}
