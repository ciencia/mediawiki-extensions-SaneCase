<?php

use MediaWiki\Config\ConfigFactory;
use MediaWiki\Page\Hook\BeforeDisplayNoArticleTextHook;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\ILoadBalancer;

class SaneCase implements BeforeDisplayNoArticleTextHook {

	private ConfigFactory $configFactory;
	private ILoadBalancer $loadBalancer;

	public function __construct( ConfigFactory $configFactory, ILoadBalancer $loadBalancer ) {
		$this->configFactory = $configFactory;
		$this->loadBalancer = $loadBalancer;
	}

	public function onBeforeDisplayNoArticleText( $article ): void {
		$title = $article->getTitle();
		$config = $this->configFactory->makeConfig( 'sanecase' );

		$originalLength = mb_strlen( $title->getDBkey() );
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		
		if ( $config->get( 'SaneCaseAutofixSpecialCharBreak' ) ) {
			// Get chances to find one page with a special character matching, there may be several results that don't match the criteria
			// while not getting a large result set
			$limit = 20;
			$titleCond = 'convert(page_title using utf8mb4) ' . $dbr->buildLike( $title->getDBkey(), $dbr->anyString() );
		} else {
			// Only want a strict case-insensitive match
			$limit = 1;
			$titleCond = [ 'convert(page_title using utf8mb4)' => $title->getDBkey() ];
		}

		// Get a list of pages which prefix matches the title
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'page_title', 'page_id' ] )
			->from( 'page' )
			->where( [ 'page_namespace' => $title->getNamespace() ] )
			->where( $titleCond )
			->limit( $limit )
			->caller( __METHOD__ )
			->fetchResultSet();

		$found = false;
		foreach ( $res as $row ) {
			if ( mb_strtolower( $row->page_title ) === mb_strtolower( $title->getDBkey() ) ) {
				// case-insensitive match
				$found = true;
			} else if (
				mb_strlen( $row->page_title ) > $originalLength &&
				preg_match( '/^_*[^a-zA-Z0-9-_~$]/', mb_substr( $row->page_title, $originalLength ) )
			) {
				// prefix match
				// the next character is a special one (not plain ascii "word" or safe punctuation)
				// optionally prefixed by a space (underscore in db), since MW trims spaces at the end
				$found = true;
			}
			if ( $found ) {
				$title = Title::newFromID( $row->page_id );
				header( 'HTTP/1.1 301 Moved Permanently' );
				header( 'Location: ' . $title->getLocalURL() );
				return;
			}
		}
	}

}
