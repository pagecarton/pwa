<?php

/**
 * PageCarton Content Management System
 *
 * LICENSE
 *
 * @category   PageCarton CMS
 * @package    Application_PWA_Script
 * @copyright  Copyright (c) 2018 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Script.php Monday 31st of December 2018 02:42PM ayoola.falola@yahoo.com $
 */

/**
 * @see PageCarton_Widget
 */

class Application_PWA_Script extends PageCarton_Widget
{
	
    /**
     * Access level for player. Defaults to everyone
     *
     * @var boolean
     */
	protected static $_accessLevel = array( 0 );
	
    /**
     * 
     * 
     * @var string 
     */
	protected static $_objectTitle = 'PWA JS Script'; 

    /**
     * 
     * 
     */
	public static function hook( $object, $method, & $data = null )
    {    
		switch( $method )
		{
			case '__construct':			
				$scope = Ayoola_Application::getUrlPrefix() . '';
				echo "
				<script>
var run = function () {
    console.log('loading');

    // Service worker for Progressive Web App
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('" . Ayoola_Application::getUrlPrefix() .  "/widgets/" . __CLASS__ .  "', {
            scope: '" . $scope .  "' // THIS IS REQUIRED FOR RUNNING A PROGRESSIVE WEB APP FROM A NON_ROOT PATH
        }).then(function(registration) {
            // Registration was successful
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function(err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err);
        });
    }
	
	//	Add to homescreen link
	let deferredPrompt;
	let addBtn;
	let aContainer;
	aContainer = document.createElement( 'div' );
	if( document.querySelector('.pc-pwa-add-button') )
	{
		addBtn = document.querySelector('.pc-pwa-add-button');
	}
	else
	{
		aContainer.setAttribute( 'style', 'position:fixed; left:0; right:0; top:0; background:#333; text-align:center;' );
		aContainer.setAttribute( 'class', '' );
		
		aDesc = document.createElement( 'p' );
		aDesc.innerHTML = '" . Application_PWA_Settings::retrieve( 'description' ) . "';
		aDesc.setAttribute( 'class', 'pc_give_space pc_container' );
		aContainer.appendChild( aDesc );

		addBtn = document.createElement( 'a' );
		addBtn.href = 'javascript:';
		addBtn.innerHTML = 'Install " . Application_PWA_Settings::retrieve( 'short_name' ) . " to Homescreen';
		addBtn.setAttribute( 'class', 'pc-give-space pc-bg-color pc-btn' );
		aContainer.appendChild( addBtn );
		
		document.body.appendChild( aContainer );
	}
	addBtn.style.display = 'none';	
	aContainer.style.display = 'none';	


	window.addEventListener('beforeinstallprompt', (e) => {
	  // Prevent Chrome 67 and earlier from automatically showing the prompt
	  e.preventDefault();
	  // Stash the event so it can be triggered later.
	  deferredPrompt = e;
	  // Update UI to notify the user they can add to home screen
	  addBtn.style.display = '';
	  aContainer.style.display = '';	
	  
	//  alert( deferredPrompt );
	//  alert( addBtn );

	  addBtn.addEventListener('click', (e) => {
		// hide our user interface that shows our A2HS button
		addBtn.style.display = 'none';	
		aContainer.style.display = 'none';	
		
		// Show the prompt
		deferredPrompt.prompt();
		// Wait for the user to respond to the prompt
		deferredPrompt.userChoice.then((choiceResult) => {
			if (choiceResult.outcome === 'accepted') {
			  console.log('User accepted the A2HS prompt');
			} else {
			  console.log('User dismissed the A2HS prompt');
			}
			deferredPrompt = null;
		  });
	  });
	});
	
	
};
window.addEventListener('load', run);
				</script>
				";
			break;
		}
	//	var_export( $object );
	//	var_export( $method );     
	//	var_export( $data );
	}

    /**
     * Performs the whole widget running process
     * 
     */
	public function init()
    {    
		try
		{ 
			$scope = Ayoola_Application::getUrlPrefix() . ( Application_PWA_Settings::retrieve( 'start_url' )  ? : '/' );
            //  Code that runs the widget goes here...
			$jsToInclude = array_values( Application_Javascript::getFilesUrl() );
			$cssToInclude = array_values( Application_Style::getFilesUrl() );
			
			//	pages
			$pages = Application_PWA_Settings::retrieve( 'pages_to_cache' ) ? : Ayoola_Page::getAll();
		//	unset( $pages['/404'] );
			foreach( $pages as $key => $each )
			{
				if( ! $pageInfo = Ayoola_Page::getInfo( $each ) OR ( ! empty( $pageInfo['auth_level'] ) && ! self::hasPriviledge( $pageInfo['auth_level'] ) ) )
				{
					unset( $pages[$key] );
				//	var_export( $each );
					continue;
				}
				$pages[$key] = Ayoola_Application::getUrlPrefix() . $each;
			}
			$pages = array_values( $pages );
			
			$defaultPages = array_values( array_unique( array( Ayoola_Application::getUrlPrefix() . '/', $scope, Ayoola_Application::getUrlPrefix() . '/offline' ) ) );  
		//	var_export( $pages );
			$cache = array_values( array_unique( array_merge( $jsToInclude, $cssToInclude, $pages ) ) );
			
			$widgets = Application_PWA_Settings::retrieve( 'widget_to_cache' );
			$widgetsText = trim( implode( '|\\/', $widgets ), '\\/|' );
			$widgetsText = $widgetsText ? '|\\/' . $widgetsText : null;
		//	foreach( $widgets as $each )
			{
				
			}
			
			
            //  Output demo content to screen
            $js = '
					/* global self, caches, fetch, URL, Response */
					\'use strict\';

					var config = {
					  version: \'achilles-y\',
					  staticCacheItems: ' . json_encode( $defaultPages ) .  ',
					  pagesToCache: ' . json_encode( $cache ) .  ',
					  cachePathPattern: /((\/Application_Javascript)|(\/layout\/)|(\/css)|(\/fonts\/)|(\/img\/)|(\.eot$)|(\.ttf$)|(\.woff$)|(\.woff2$)|(\.svg$)|(\.html$)' . $widgetsText .  ')/,
					  offlineImage: \'<svg role="img" aria-labelledby="offline-title"\'
						+ \' viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">\'
						+ \'<title id="offline-title">Offline</title>\'
						+ \'<g fill="none" fill-rule="evenodd"><path fill="#D8D8D8" d="M0 0h400v300H0z"/>\'
						+ \'<text fill="#9B9B9B" font-family="Times New Roman,Times,serif" font-size="72" font-weight="bold">\'
						+ \'<tspan x="93" y="172">offline</tspan></text></g></svg>\',
					  offlinePage: \'' . Ayoola_Application::getUrlPrefix() . '/offline\'
					};

					function cacheName (key, opts) {
					  return `${opts.version}-${key}`;
					}

					function addToCache (cacheKey, request, response) {
					  if (response.ok) {
						var copy = response.clone();
						caches.open(cacheKey).then( cache => {
						  cache.put(request, copy);
						});
					  }
					  return response;
					}

					function fetchFromCache (event) {
					  return caches.match(event.request).then(response => {
						if (!response) {
						  throw Error(`${event.request.url} not found in cache`);
						}
						return response;
					  });
					}

					function offlineResponse (resourceType, opts) {
						 console.log( resourceType );
						 console.log( opts.offlinePage );
					  if (resourceType === \'image\') {
						return new Response(opts.offlineImage,
						  { headers: { \'Content-Type\': \'image/svg+xml\' } }
						);
					  } else if (resourceType === \'content\') {
						return caches.match(opts.offlinePage);
					  }
					  return undefined;
					}

					self.addEventListener(\'install\', event => {
					  function onInstall (event, opts) {
						var cacheKey = cacheName(\'static\', opts);
						return caches.open(cacheKey)
						  .then(cache => cache.addAll(opts.staticCacheItems).then(function() {
						  // requests have been added to the cache
						   console.log( "Requests added to cache" );
						}));
					  }
	
					  event.waitUntil(
						onInstall(event, config).then( () => self.skipWaiting() )
					  );
					});

					self.addEventListener(\'activate\', event => {
					  function onActivate (event, opts) {
						return caches.keys()
						  .then(cacheKeys => {
							var oldCacheKeys = cacheKeys.filter(key => key.indexOf(opts.version) !== 0);
							var deletePromises = oldCacheKeys.map(oldKey => caches.delete(oldKey));
							return Promise.all(deletePromises);
						  });
					  }

					  event.waitUntil(
						onActivate(event, config)
						  .then( () => self.clients.claim() )
					  );
					});
					self.addEventListener( \'fetch\', event => {

					  function shouldHandleFetch (event, opts) {
						var request            = event.request;
						var url                = new URL(request.url);
						console.log( url.pathname );
					//	console.log( request.method );
					//	console.log( opts.cachePathPattern.test(url.pathname) );
						console.log( opts.pagesToCache.indexOf( url.pathname ) );
					//	console.log( url.origin === self.location.origin );
						var criteria           = {
						  matchesPathPattern: ( opts.cachePathPattern.test(url.pathname) || ( opts.pagesToCache.indexOf( url.pathname )  !== -1 ) || ( opts.staticCacheItems.indexOf( url.pathname )  !== -1 ) ),
						  isGETRequest      : request.method === \'GET\'
						//  isFromMyOrigin    : url.origin === self.location.origin
						};
						var failingCriteria    = Object.keys(criteria)
						  .filter(criteriaKey => !criteria[criteriaKey]);
						return !failingCriteria.length;
					  }

					  function onFetch (event, opts) {
						var request = event.request;
						var acceptHeader = request.headers.get(\'Accept\');
						var resourceType = \'static\';
						var cacheKey;

						if (acceptHeader.indexOf(\'text/html\') !== -1) {
						  resourceType = \'content\';
						} else if (acceptHeader.indexOf(\'image\') !== -1) {
						  resourceType = \'image\';
						}

						cacheKey = cacheName(resourceType, opts);

						if (resourceType === \'content\') {
						//   console.log( " fetching " + resourceType + " - " + request.url );
						  event.respondWith(
							fetch(request)
							  .then(response => addToCache(cacheKey, request, response))
							  .catch(() => fetchFromCache(event))
							  .catch(() => offlineResponse(resourceType, opts))
						  );
						} else {
						//   console.log( " fetching " + resourceType + " - " + request.url );
						  event.respondWith(
							fetchFromCache(event)
							  .catch(() => fetch(request))
								.then(response => addToCache(cacheKey, request, response))
							  .catch(() => offlineResponse(resourceType, opts))
						  );
						}
					  }
					  
					  function interfereInFetch (event, opts) {
						var request = event.request;
						var acceptHeader = request.headers.get(\'Accept\');
						var resourceType = \'static\';
						var cacheKey;

						if (acceptHeader.indexOf(\'text/html\') !== -1) {
						  resourceType = \'content\';
						} else if (acceptHeader.indexOf(\'image\') !== -1) {
						  resourceType = \'image\';
						}

					//	cacheKey = cacheName(resourceType, opts);

						  event.respondWith(
							fetch(request)
							 // .then(response => addToCache(cacheKey, request, response))
							//  .catch(() => fetchFromCache(event))
							  .catch(() => offlineResponse(resourceType, opts))
						  );
					  }
					  if( shouldHandleFetch(event, config) ) 
					  {
						onFetch( event, config );
					  }
					//  elseif( stillInterfereInFetch(event, config) )
					  else
					  {
						interfereInFetch( event, config );
					  }
					});
			';
			header('Content-Type: text/javascript; charset=utf-8');
			header( 'Service-Worker-Allowed: ' . ( Ayoola_Application::getUrlPrefix() ? : '/' ) .  '' );
			echo $js;
			exit();

             // end of widget process
          
		}  
		catch( Exception $e )
        { 
            //  Alert! Clear the all other content and display whats below.
        //    $this->setViewContent( self::__( '<p class="badnews">' . $e->getMessage() . '</p>' ) ); 
            $this->setViewContent( self::__( '<p class="badnews">Theres an error in the code</p>' ) ); 
            return false; 
        }
	}
	// END OF CLASS
}
