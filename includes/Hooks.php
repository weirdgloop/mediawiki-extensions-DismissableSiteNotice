<?php

namespace MediaWiki\Extension\DismissableSiteNotice;

use Html;
use MediaWiki\Hook\SiteNoticeAfterHook;
use MediaWiki\Parser\Sanitizer;
use Skin;
use Xml;

class Hooks implements SiteNoticeAfterHook {

	/**
	 * @param string &$notice
	 * @param Skin $skin
	 * @suppress SecurityCheck-DoubleEscaped
	 */
	public function onSiteNoticeAfter( &$notice, $skin ) {
		global $wgMajorSiteNoticeID, $wgDismissableSiteNoticeForAnons;
		if ( method_exists( $skin, 'getVersion' ) ) {
			// does the skin support versioning and if so does it provide dismissable site notices?
			if ( $skin->getVersion() === 2 ) {
				return;
			}
		}

		if ( trim( Sanitizer::removeHTMLcomments( $notice ) ) === '' ) {
			return;
		}

		// Dismissal for anons is configurable
		if ( $wgDismissableSiteNoticeForAnons || $skin->getUser()->isRegistered() ) {
			// Cookie value consists of two parts
			$major = (int)$wgMajorSiteNoticeID;
			$minor = (int)$skin->msg( 'sitenotice_id' )->inContentLanguage()->text();

			$out = $skin->getOutput();
			$out->addModuleStyles( 'ext.dismissableSiteNotice.styles' );
			$out->addModules( 'ext.dismissableSiteNotice' );
			$out->addJsConfigVars( 'wgSiteNoticeId', "$major.$minor" );

			// Svg of x for icon
			$icon = '<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>close</title><path d="M3.636 2.224l14.142 14.142-1.414 1.414L2.222 3.638z" /><path d="M17.776 3.636L3.634 17.778 2.22 16.364 16.362 2.222z"/></svg>';
			$close = Html::rawElement( 'div', [ 'class' => 'mw-dismissable-notice-close' ],
				$skin->msg( 'sitenotice_close-brackets' )
					->rawParams(
						Html::rawelement( 'a', [ 'tabindex' => '0', 'role' => 'button' ],
							Html::rawelement( 'span', [ 'class' => 'dismiss-icon'], $icon).
							Html::element('span', [ 'class' => 'dismiss-text'], $skin->msg( 'sitenotice_close' )->text())
						)
					)
					->escaped()
			);

			if ( strstr($notice, '%CLOSE%') ) {
				// Close position given
				$notice = str_replace('%CLOSE%', $close, $notice);
				$notice = Html::rawElement( 'div', [ 'class' => 'mw-dismissable-notice' ],
					Html::rawElement( 'div', [ 'class' => 'mw-dismissable-notice-body' ], $notice )
				);
			} else {
				// No close position given
				$notice = Html::rawElement( 'div', [ 'class' => 'mw-dismissable-notice' ],
					$close .
					Html::rawElement( 'div', [ 'class' => 'mw-dismissable-notice-body' ], $notice )
				);
			}
		}

		if ( $skin->getUser()->isAnon() ) {
			// If there's no nonce (false), pass null to Html::inlineScript
			$nonce = $skin->getOutput()->getCSP()->getNonce() ?: null;

			// Hide the sitenotice from search engines (see bug T11209 comment 4)
			// NOTE: Is this actually effective?
			// NOTE: Avoid document.write (T125323)
			// NOTE: Must be compatible with JavaScript in ancient Grade C browsers.
			$jsWrapped =
				'<div id="mw-dismissablenotice-anonplace"></div>' .
				Html::inlineScript(
					'(function(){' .
					'var node=document.getElementById("mw-dismissablenotice-anonplace");' .
					'if(node){' .
					// Replace placeholder with parsed HTML from $notice.
					// Setting outerHTML is supported in all Grade C browsers
					// and gracefully fallsback to just setting a property.
					// It is short for:
					// - Create temporary element or document fragment
					// - Set innerHTML.
					// - Replace node with wrapper's child nodes.
					'node.outerHTML=' . Xml::encodeJsVar( $notice ) . ';' .
					'}' .
					'}());',
					$nonce
				);
			$notice = $jsWrapped;
		}
	}
}
