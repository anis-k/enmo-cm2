var $j = jQuery.noConflict();
var angularGlobals = {};
var alreadyLoaded = false;
function triggerAngular(locationToGo) {
    var myApp = $j('<my-app style="height: 100%;display:none;"></my-app>');
    myApp.appendTo('body');
    $j.ajax({
        url      : '../../rest/initialize',
        type     : 'GET',
        dataType : 'json',
        success: function(answer) {
            angularGlobals = answer;

            if (!alreadyLoaded) {
                var head = document.getElementsByTagName('head')[0];
                $j('#maarch_content').remove();
                var loading = $j('<div id="loadingAngularContent" style="color: #666;height: 100%;padding: 0;margin: 0;display: flex;align-items: center;justify-content: center;"><div style="opacity:0.5;display: flex;justify-content: center;padding: 5px;height: 20px;margin: 10px;line-height: 20px;font-weight: bold;font-size: 2em;text-align: center;"><div class="lds-ring"><div></div><div></div><div></div><div></div></div><div style=\'font-family: Roboto,"Helvetica Neue",sans-serif;\'>Chargement en cours ...</div></div></div>');
                loading.appendTo('body');

                answer['scriptsToinject'].forEach(function(element, i) {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = "../../dist/" + element;

                    if ((i + 1) === answer['scriptsToinject'].length) {
                        script.onreadystatechange = changeLocationToAngular(locationToGo);
                        script.onload = changeLocationToAngular(locationToGo);
                    }

                    // Fire the loading
                    if (i === 2) {
                        setTimeout(function () {
                            head.appendChild(script);
                        }, 400);
                    } else {
                        head.appendChild(script);
                    }
                });

                var meta = document.createElement('meta');
                meta.name = 'viewport';
                meta.content = "width=device-width, initial-scale=1.0";
                head.appendChild(meta);

                alreadyLoaded = true;
            } else {
                location.href = locationToGo;
            }
        }
    });
}

function changeLocationToAngular(locationToGo) {
    location.href = locationToGo;
}

function lockDocument(resId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=docLocker',
        type : 'POST',
        data: {
            AJAX_CALL  : true,
            lock       : true,
            res_id     : resId
        },
        success: function(result){
        }
    });
}

function unlockDocument(resId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=docLocker',
        type : 'POST',
        data: {
            AJAX_CALL  : true,
            unlock     : true,
            res_id     : resId
        },
        success: function(result) {
        }
    });
}

function islockForSignatureBook(resId, basketId, groupId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=docLocker',
        type : 'POST',
        data: {
            AJAX_CALL  : true,
            isLock     : true,
            res_id     : resId
        },
        success: function(result) {
            var response = JSON.parse(result);

            if (response.lock) {
                alert("Courrier verrouillé par " + response.lockBy);
            } else {
                triggerAngular("#/groups/" + groupId + "/baskets/" + basketId + "/signatureBook/" + resId);
            }
        }
    });
}

var disablePrototypeJS = function (method, pluginsToDisable) {
    var handler = function (event) {
        event.target[method] = undefined;
        setTimeout(function () {
            delete event.target[method];
        }, 0);
    };
    pluginsToDisable.each(function (plugin) {
        $j(window).on(method + '.bs.' + plugin, handler);
    });
};

/*if (Prototype.BrowserFeatures.ElementExtensions) {
    //FIX PROTOTYPE CONFLICT
    var pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover','tab'];
    disablePrototypeJS('show', pluginsToDisable);
    disablePrototypeJS('hide', pluginsToDisable);
}*/

function setAttachmentInSignatureBook(id, isVersion) {
    $j.ajax({
        url      : '../../rest/attachments/' + id + '/inSignatureBook',
        type     : 'PUT',
        dataType : 'json',
        data: {
            isVersion   : isVersion
        },
        success: function(answer) {
            if (typeof window.parent['angularSignatureBookComponent'] !== "undefined") {
                window.parent.angularSignatureBookComponent.componentAfterAttach("left");
            }
        }, error: function(err) {
            alert("Une erreur s'est produite : " + err.responseJSON.exception[0].message);
        }
    });
}

function setSessionForSignatureBook(resId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=setSession',
        type : 'POST',
        data: {
            resId     : resId
        },
        success: function(result) {
        }
    });
}

function displayThumbnail(resId)
{
    $j('#thumb_' + resId).html('<img src="../../rest/res/' + resId + '/thumbnail">');
}

var koKeys = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];
var koNb = 0;
$j(document).keydown(function (e) {
    if (e.keyCode === koKeys[koNb++]) {
        if (koNb === koKeys.length) {
            $j('my-app').hide();
            var img = $j('<img id="konami" style="position: absolute; display: none">'); //Equivalent: $(document.createElement('img'))
            img.attr('src', 'img/konami.png');
            img.appendTo('body');
            var audio = new Audio('img/konami.mp3');
            audio.play();
            var konami = $j("#konami");
            konami.css('top', '200px');
            konami.show();
            var pos = 100;
            var rot = 0;
            var id = setInterval(frame, 10);
            function frame() {
                if (pos > 1400) {
                    clearInterval(id);
                    konami.remove();
                    konami.css('left', '200px');
                    $j('my-app').show();
                } else {
                    pos += 5;
                    konami.css('left', pos + 'px');
                    if (pos == 0 || pos == 400) {
                        konami.css({'-webkit-transform' : 'rotate(-15deg)',
                        '-moz-transform' : 'rotate(-15deg)',
                        '-ms-transform' : 'rotate(-15deg)',
                        'transform' : 'rotate(-15deg)'});
                    } else if (pos == 200 || pos == 600) {
                        konami.css({'-webkit-transform' : 'rotate(15deg)',
                        '-moz-transform' : 'rotate((15degg)',
                        '-ms-transform' : 'rotate((15deg)',
                        'transform' : 'rotate((15deg)'});  
                    }
                    if(pos > 800) {
                        rot += 5;
                        konami.css({'-webkit-transform' : 'rotate('+ rot +'deg)',
                        '-moz-transform' : 'rotate('+ rot +'deg)',
                        '-ms-transform' : 'rotate('+ rot +'deg)',
                        'transform' : 'rotate('+ rot +'deg)'});
                    }
                    
                }
            }
            koNb = 0;
        }
    } else {
        koNb = 0;
    }
});
