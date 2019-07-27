

function cacheName(key, opts) {
    return `${opts.version}-${key}`;
}
function getResourceType( request )
{
    var acceptHeader = request.headers.get('Accept');
    var resourceType = 'static';
    var cacheKey;

    if (acceptHeader.indexOf('text/html') !== -1) {
        resourceType = 'content';
    } else if (acceptHeader.indexOf('image') !== -1) {
        resourceType = 'image';
    }
    return resourceType;
}

function shouldAcceptResponse(response) 
{
    if( ! response.url )
    {
        return false
    }
    try
    {
        var result = response.status !== 0 && response.status < 400 || response.type === "opaque" || response.type === "opaqueredirect";
        return result;
    }
    catch
    {
        return result;
    }
}

function pageNotFound( response ) 
{
    try
    {
        var result =  ( response.status >= 400 );
        return result;
    }
    catch
    {
        return result;
    }
}

function serverError( response ) 
{
    try
    {
        if( response.status >= 500 )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    catch
    {
        return true;
    }
}

function addToCache(cacheKey, request, response) {
    if (response.ok) {
        var copy = response.clone();
        caches.open(cacheKey).then(cache => {
            cache.put(request, copy);
        });
    }
    return response;
}

function fetchFromCache(event) {
    return caches.match(event.request).then(response => {
        if (!response) {
            throw Error(`${event.request.url} not found in cache`);
        }
        return response;
    });
}

function offlineResponse(resourceType, opts) {
    if (resourceType === 'image') {
        return new Response(opts.offlineImage,
            { headers: { 'Content-Type': 'image/svg+xml' } }
        );
    } else if (resourceType === 'content') {
        return caches.match(opts.offlinePage);
    }
    return undefined;
}

function serverErrorResponse(resourceType, opts) {
    console.log( 'Getting ' + resourceType + ' for ' );
    if (resourceType === 'image') {
        return new Response(opts.offlineImage,
            { headers: { 'Content-Type': 'image/svg+xml' } }
        );
    } else if (resourceType === 'content') {
        console.log( opts.serverErrorPage );
        return caches.match(opts.serverErrorPage);
    }
    return undefined;
}

self.addEventListener('install', event => {
    function onInstall(event, opts) {
        var cacheKey = cacheName('static', opts);
        return caches.open(cacheKey)
            .then(cache => cache.addAll(opts.staticCacheItems).then(function () {
                // requests have been added to the cache
            }));
    }

    event.waitUntil(
        onInstall(event, config).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    function onActivate(event, opts) {
        return caches.keys()
            .then(cacheKeys => {
                var oldCacheKeys = cacheKeys.filter(key => key.indexOf(opts.version) !== 0);
                var deletePromises = oldCacheKeys.map(oldKey => caches.delete(oldKey));
                return Promise.all(deletePromises);
            });
    }

    event.waitUntil(
        onActivate(event, config)
            .then(() => self.clients.claim())
    );
});

async function fetchIt( options )
{
    try 
    {        
    //    console.log( 'Fetching ' + request.url );
        const response = await fetch( options.request );
        if( ! response || ! response.url )
        {
         //   console.log( 'Error response from ' + request.url );
            return response;
        }
        // check if the response is valid since fetch doesn’t throw if the server
        // gives back a non 200-299 status code
        console.log( 'should we accept ' + response.url );
        if( shouldAcceptResponse( response ) ) 
        {
            console.log( response );
            return response;
        }
        else
        {
        //    console.log( 'Not accepting error data from ' + response.url );
            if( serverError( response ) )
            {
                console.log( 'Server error ' + response.url );
                var resourceType = getResourceType( options.request );
                return serverErrorResponse( resourceType, options.opts )
            }
            return response;
            
        }
    } 
    catch 
    {
        return response;
        // catch network errors
    }
}
self.addEventListener('notificationclose', function(e) {
    var notification = e.notification;
    var primaryKey = notification.data.primaryKey;
  
    console.log('Closed notification: ' + primaryKey);
  });

self.addEventListener('notificationclick', function(e) {
  var notification = e.notification;
  var primaryKey = notification.data.primaryKey;
  var action = e.action;

  if (action === 'close') {
    notification.close();
  } else {
    clients.openWindow( 'http://www.example.com' );
    notification.close();
  }
});
self.addEventListener('push', function(e) {

    e.waitUntil(
        displayNotification( { "title": "Notification pushed!", "body": "Great! You are now set to be getting up-to-date information from us directly" } )
    );
  });

self.addEventListener('fetch', event => {

    function shouldHandleFetch(event, opts) {
        var request = event.request;
        var url = new URL(request.url);
        var criteria = {
            matchesPathPattern: (opts.cachePathPattern.test(url.pathname) || (opts.pagesToCache.indexOf(url.pathname) !== -1) || (opts.staticCacheItems.indexOf(url.pathname) !== -1)),
            isGETRequest: request.method === 'GET',
            isFromMyOrigin: url.origin === self.location.origin
        };
        var failingCriteria = Object.keys(criteria)
            .filter(criteriaKey => !criteria[criteriaKey]);
        return !failingCriteria.length;
    }

    function onFetch(event, opts) {
        var request = event.request;
        var acceptHeader = request.headers.get('Accept');
        var resourceType = 'static';
        var cacheKey;

        if (acceptHeader.indexOf('text/html') !== -1) {
            resourceType = 'content';
        } else if (acceptHeader.indexOf('image') !== -1) {
            resourceType = 'image';
        }

        cacheKey = cacheName(resourceType, opts);
        if (resourceType === 'content') {
            event.respondWith(

                fetchIt( { "request": request, "opts": opts } )
                    .then(response => addToCache(cacheKey, request, response))
                    .catch(() => fetchFromCache(event))
                    .catch(() => offlineResponse(resourceType, opts))
            );
        } else {
            event.respondWith(
                fetchFromCache(event)
                    .catch(() => fetchIt( { "request": request, "opts": opts } ) )
                    .then(response => addToCache(cacheKey, request, response))
                    .catch(() => offlineResponse(resourceType, opts))
            );
        }
    }
    function interfereInFetch(event, opts) {
        var request = event.request;
        var resourceType = getResourceType( request );

        event.respondWith(
            fetchIt( { "request": request, "opts": opts } )
                .catch(() => offlineResponse(resourceType, opts))
        );
    }
    if (shouldHandleFetch(event, config)) {
        onFetch(event, config);
    }
    else {
        interfereInFetch(event, config);
    }
});
