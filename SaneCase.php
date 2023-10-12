<?php

use MediaWiki\MediaWikiServices;

class SaneCase {
	public static function onBeforeDisplayNoArticleText( $article ) {
		$title = $article->getTitle();
		$loadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $loadBalancer->getConnectionRef( DB_REPLICA );

		$pageRow = $dbr->newSelectQueryBuilder()
			->select( 'page_id' )
			->from( 'page' )
			->where( [
				'page_namespace' => $title->getNamespace(),
				'convert(page_title using utf8mb4)' => $title->getDBkey(),
			] )
			->caller( __METHOD__ )->fetchRow();

		if ( $pageRow ) {
			$title = Title::newFromID( $pageRow->page_id );
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $title->getLocalURL() );
		}
	}
}
