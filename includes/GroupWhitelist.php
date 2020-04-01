<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\GroupWhitelist;

use Config;
use MediaWiki\MediaWikiServices;
use Title;
use User;
use WikiPage;

class GroupWhitelist {

	/** @var GroupWhitelist */
	private static $instance;
	/** @var Config */
	private $config;
	/** @var int[] */
	private $whitelistedIds;

	public static function getInstance() {
		if( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->config = MediaWikiServices::getInstance()->getMainConfig();
		// TODO: replace with the getWhitelist
		$this->whitelistedIds = $this->getWhitelist();
	}

	private function parseWhitelist() {
		$whitelistedIds = [];
		if ( $this->isEnabled() ) {
			$targetTitle = Title::newFromText( $this->config->get('GroupWhitelistSourcePage') );
			if( $targetTitle->exists() ) {
				$page = WikiPage::factory( $targetTitle );
				$text = $page->getContent()->getWikitextForTransclusion();
				$entries = explode("\n", $text);
				foreach ($entries as $entry) {
					$t = Title::newFromText( $entry );
					if ( $t && $t->exists() ) {
						$whitelistedIds[] = $t->getArticleID();
					}
				}
			}
		}
		return $whitelistedIds;
	}

	/**
	 * @return int[]
	 */
	private function getWhitelist() {
		$key = wfMemcKey( 'groupwhitelist', 'whitelistids' );
		$cache = wfGetCache( CACHE_ANYTHING );
		$result = $cache->get( $key );
		if ( !$result ) {
			$result = $this->parseWhitelist();
			$cache->set( $key, implode( ',', $result ) );
		}
		return explode( ',', $result );
	}

	/**
	 * Is properly configured and allowed to run
	 * @return bool
	 */
	public function isEnabled() {
		if(
			!count($this->config->get('GroupWhitelistRights')) ||
			!$this->config->get('GroupWhitelistGroup') ||
			!$this->config->get('GroupWhitelistSourcePage')
		) {
			return false;
		}
		return true;
	}

	/**
	 * Check if user and title are subjects for the override
	 *
	 * @param User $user
	 * @param Title $title
	 *
	 * @return bool
	 */
	public function isMatch( $user, $title, $action = null ) {
		// Check if user has the target group
		if ( !in_array( $this->config->get('GroupWhitelistGroup'), $user->getEffectiveGroups() ) ) {
			return false;
		}
		// Check if target page is whitelisted
		if ( !in_array( $title->getArticleID(), $this->whitelistedIds ) ) {
			return false;
		}
		// Check if target action needs to be overridden
		if( $action && !in_array( $action, $this->config->get('GroupWhitelistRights') ) ) {
			return false;
		}
		return true;
	}

	public function getGrants() {
		return $this->config->get('GroupWhitelistRights');
	}

}
