let pwaSW;
let applicationServerKey = 'BMBlr6YznhYMX3NgcWIDRxZXs0sh7tCv7_YCsWcww0ZCv9WGg-tRCXfMEHTiBPCksSqeve1twlbmVAZFv7GSuj0';
var runPWA = function ( config ) {


    // Service worker for Progressive Web App
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register( config.serviceWorkerUrl, {
            scope: config.scope // THIS IS REQUIRED FOR RUNNING A PROGRESSIVE WEB APP FROM A NON_ROOT PATH
        }).then(function (registration) {

            // Registration was successful
            pwaSW = registration;

            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function (err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err);
        });
    }

    //	Add to homescreen link
    let deferredPrompt;
    let addBtn;
    let aContainer;
    aContainer = document.createElement('div');
    if (document.querySelector('.pc-pwa-add-button')) {
        addBtn = document.querySelector('.pc-pwa-add-button');
    }
    else {
        aContainer.setAttribute('style', 'position:fixed; left:0; right:0; top:0; background:#333; text-align:center;padding:3em;z-index:100000;');
        aContainer.setAttribute('class', '');

        title = document.createElement('p');
        title.innerHTML = config.shortname;
        title.setAttribute( 'style', 'color:white;font-size:large;');
        title.setAttribute('class', 'pc_give_space pc_container');
        aContainer.appendChild(title);


        aDesc = document.createElement('p');
        aDesc.innerHTML = config.description;
        aDesc.setAttribute( 'style', 'color:white;');
        aDesc.setAttribute('class', 'pc_give_space pc_container');
        aContainer.appendChild(aDesc);

        addBtn = document.createElement('a');
        addBtn.href = 'javascript:';
        addBtn.innerHTML = 'Download to Homescreen <i class="fa fa-download pc_give_space"></i>';
        addBtn.setAttribute('class', 'pc_give_space pc-bg-color pc-btn');
        aContainer.appendChild(addBtn);

        close = document.createElement('a');
        close.href = 'javascript:';
        close.innerHTML = '<i class="fa fa-remove pc_give_space"></i>';
        close.setAttribute( 'style', 'position:fixed; right:5px; top:5px; z-index:200000; font-size: large; ');
        close.setAttribute('class', 'pc_give_space pc-btn-small');
        aContainer.appendChild(close);
        removeCallToInstall(aContainer);

        document.body.appendChild(aContainer);
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
            initialiseState();
            authorizeNotification();
            deferredPrompt.prompt();
            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the A2HS prompt');
                    
                    authorizeNotification();

                    pwaSW.pushManager.getSubscription().then(function(sub) {
                        if (sub === null) {
                          // Update UI to ask user to register for Push
                          console.log('Not subscribed to push service yet!');

                          initialiseState();

                        } else {
                          // We have a subscription, update the database
                          console.log( 'Subscription object: ', sub );
                        }
                      });

                } else {
                    console.log('User dismissed the A2HS prompt');
                }
                deferredPrompt = null;
            });
        });
    });

    // Once the service worker is registered set the initial state
    function initialiseState() {
        // Are Notifications supported in the service worker?
        if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
          console.warn('Notifications aren\'t supported.');
          return;
        }
      
        // Check the current Notification permission.
        // If its denied, it's a permanent block until the
        // user changes the permission
        if (Notification.permission === 'denied') {
          console.warn('The user has blocked notifications.');
          return;
        }
      
        // Check if push messaging is supported
        if (!('PushManager' in window)) {
          console.warn('Push messaging isn\'t supported.');
          return;
        }
      
        // We need the service worker registration to check for a subscription
        navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
          // Do we already have a push message subscription?
         
          serviceWorkerRegistration.pushManager.getSubscription()
            .then(function(subscription) {
    
              // Enable any UI which subscribes / unsubscribes from
              // push messages.
              var isPushEnabled = false;
    
        
              var pushButton = document.createElement( 'button' );
              pushButton.innerHTML = 'Enable Push Notification';
              pushButton.cssText = 'position:fixed; bottom: 10; left: 10;';
              pushButton.className = 'js-push-button';
              document.body.appendChild( pushButton );
            
              window.addEventListener( 'load', function() {
                pushButton.addEventListener('click', function() {
                  if (isPushEnabled) {
                    unsubscribe();
                  } else {
                    subscribe();
                  }
                });
    
              });
    
              pushButton.disabled = false;
      
              if (!subscription) {
    
                push_subscribe();
                //    serviceWorkerRegistration.pushManager.subscribe()
                // We aren't subscribed to push, so set UI
                // to allow the user to enable push
                return;
              }
    
            //  console.log( subscription ); 
      
              // Keep your server in sync with the latest subscriptionId
            //  sendSubscriptionToServer(subscription);
                push_updateSubscription()
      
              // Set your UI to show they have subscribed for
              // push messages
              pushButton.textContent = 'Disable Push Messages';
              isPushEnabled = true;
            })
            .catch(function(err) {
              console.warn('Error during getSubscription()', err);
            });
        });
      }
    function urlBase64ToUint8Array(base64String) 
    {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
    
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
    function push_sendSubscriptionToServer( subscription, method ) 
    {
        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
    
        console.log( subscription )
        var url = config.scope + '/widgets/Application_PWA_Push_Subscription_Creator';
        var data = {
            endpoint: subscription.endpoint,
            publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
            contentEncoding: contentEncoding,
            method: method
          };
        data = JSON.stringify( data )
        var ajax = ayoola.xmlHttp.fetchLink( { "url": url, "data": data } )
        return ajax
    }
    function push_subscribe() {
    
        return checkNotificationPermission()
          .then(() => navigator.serviceWorker.ready)
          .then(serviceWorkerRegistration =>
            serviceWorkerRegistration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
            })
          )
          .then(subscription => {
            // Subscription was successful
            // create subscription on your server
            return push_sendSubscriptionToServer(subscription, 'POST');
          })
          .then(subscription => subscription) // update your UI
          .catch(e => {
            if (Notification.permission === 'denied') {
              // The user denied the notification permission which
              // means we failed to subscribe and the user will need
              // to manually change the notification permission to
              // subscribe to push messages
              console.warn('Notifications are denied by the user.');
            } else {
              // A problem occurred with the subscription; common reasons
              // include network errors or the user skipped the permission
              console.error('Impossible to subscribe to push notifications', e);
            }
          });
    }
    
    function push_updateSubscription() 
    {
        navigator.serviceWorker.ready
          .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
          .then(subscription => {
    
            if (!subscription) {
              // We aren't subscribed to push, so set UI to allow the user to enable push
              return;
            }
    
            // Keep your server in sync with the latest endpoint
            return push_sendSubscriptionToServer(subscription, 'POST');
          })
          .then( subscription => subscription ) // Set your UI to show they have subscribed for push messages
          .catch(e => {
            console.error('Error when updating the subscription', e);
          });
    }    

};
function removeCallToInstall(aContainer) {
    close.addEventListener('click', function () {
        if (aContainer && aContainer.parentNode)
            aContainer.parentNode.removeChild(aContainer);
    });
}
function displayNotification( info ) {
    if ( Notification.permission == 'granted') {
        navigator.serviceWorker.getRegistration().then(function(reg) {
            var options = {
                body: info.body,
                icon: info.icon ? info.icon : pwaScope + '/img/logo.png',
                vibrate: info.vibrate ? info.vibrate : [100, 50, 100],
                data: info.data ? info.data : { dateOfArrival: Date.now(),
                    primaryKey: 1 },
                actions: info.actions ? info.actions : [
                    {action: 'view', title: 'Details',
                      icon: '/favicon.ico'},
                    {action: 'close', title: 'Close',
                      icon: '/favicon.ico'},
                  ]
              };

        reg.showNotification( info.title, options );
        });
    }
    }

authorizeNotification = function()
{
    //  Let's go for notification
    Notification.requestPermission(function(status) {
        console.log('Notification permission status:', status);
        displayNotification( { "title": "Notifications now up!", "body": "Great! You are now set to be getting up-to-date information from us" } )

    });    
}
if( 'Notification' in window && navigator.serviceWorker ) 
{
//   authorizeNotification();
}
