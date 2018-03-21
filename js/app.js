(function(){

    var app = angular.module('cpm', [
        'ngSanitize', 'ngRoute', 'cpm.pagesctrl', 'cpm.indexctrl', 'cpm.cpmidxctrl',
        'cpm.menucpm', 'cpm.usrctrl', 'cpm.showtab', 'cpm.menusrvc', 'cpm.menuctrl',
        'cpm.empresasrvc', 'cpm.empresactrl', 'cpm.monedactrl', 'cpm.monedasrvc', 'xeditable',
        'cpm.editrowform', 'cpm.setempre', 'ui.bootstrap', 'datatables', 'datatables.bootstrap',
        'cpm.datatablewrapper', 'fcsa-number', 'angular-confirm', 'cpm.getbyidfltr', 'cpm.padfltr',
        'adaptv.adaptStrap', 'cpm.getbycodctafltr', 'cpm.jsreportsrvc', 'angular.chosen', 'cpm.cpmchosen',
        'angucomplete-alt', 'ui.select', 'thatisuday.ng-image-gallery', 'cpm.transegcasoctrl', 'cpm.casosrvc',
        'cpm.cuentacctrl', 'cpm.cuentacsrvc', 'cpm.bancoctrl', 'cpm.bancosrvc', 'cpm.proveedorctrl', 'cpm.proveedorsrvc',
        'cpm.tranbancctrl', 'cpm.tranbacsrvc',
        'cpm.conciliactrl', 'cpm.tipodocsoptbsrvc', 'cpm.tipomovtranbansrvc', 'cpm.pcontsrvc', 'cpm.detcontsrvc',
        'cpm.compractrl', 'cpm.comprasrvc', 'cpm.tipocomprasrvc', 'cpm.periodocontctrl',
        'cpm.tipoconfigcontasrvc', 'cpm.tranpagosctrl', 'cpm.tranpagossrvc', 'cpm.trandirectactrl', 'cpm.directasrvc',
        'cpm.rptcatbcoctrl', 'cpm.rptcatprovctrl', 'cpm.rptfactprovctrl', 'cpm.rptdetcontfactctrl', 'cpm.rpthistpagosctrl',
        'cpm.rptcorrchctrl', 'cpm.rptdocscirculactrl', 'cpm.rptdetcontdocsctrl', 'cpm.rptestadoctactrl', 'cpm.paissrvc',
        'cpm.titulosrvc', 'cpm.periodicidadsrvc', 'cpm.cobroprimrentasrvc', 'cpm.tipofinancierasrvc', 'cpm.financierasrvc',
        'cpm.mntgenclientesctrl', 'cpm.promotorsrvc', 'cpm.clientesrvc', 'cpm.provequiposrvc', 'cpm.promclictrl',
        'cpm.proveqctrl', 'cpm.contratoctrl', 'cpm.contratorvc', 'cpm.facturacionsrvc', 'cpm.facturacionctrl',
        'cpm.sectorctrl', 'cpm.sectorsrvc', 'cpm.tipocambiosrvc', 'cpm.facturactrl',
        'cpm.facturasrvc', 'cpm.reembolsoctrl', 'cpm.reembolsosrvc', 'cpm.tiporeembolsosrvc',
        'cpm.tipofacturasrvc',
        'cpm.beneficiarioctrl', 'cpm.beneficiariosrvc', 'cpm.razonanulacionsrvc',
        'cpm.razonanulacionctrl', 'cpm.rptbalsalsrvc', 'cpm.rptbalsalctrl', 'cpm.rptlibcompsrvc', 'cpm.rptlibcompctrl',
        'cpm.tipocombsrvc', 'cpm.rptlibmaysrvc', 'cpm.rptlibmayctrl', 'cpm.rptestressrvc', 'cpm.rptestresctrl',
        'cpm.rptbalgensrvc', 'cpm.rptbalgenctrl', 'cpm.tipocompractrl', 'cpm.ventasrvc', 'cpm.ventactrl', 'cpm.rptlibventasrvc',
        'cpm.rptlibventactrl', 'cpm.isrctrl',
        'cpm.rptlibdiactrl', 'cpm.reciboprovsrvc',
        'cpm.reciboprovctrl', 'cpm.reciboclisrvc', 'cpm.reciboclictrl', 'cpm.rptconciliabcosrvc', 'cpm.rptconciliabcoctrl',
        'cpm.rptsaldoclisrvc', 'cpm.rptsaldoclictrl','cpm.rptsaldoprovsrvc', 'cpm.rptsaldoprovctrl','cpm.rptanticlisrvc',
        'cpm.rptanticlictrl','cpm.rptantiprovsrvc', 'cpm.rptantiprovctrl','cpm.rptecuentaprovsrvc', 'cpm.rptecuentaprovctrl',
        'cpm.rptecuentaclisrvc', 'cpm.rptecuentaclictrl',
        'cpm.rptintegraclictrl', 'cpm.fuentecasoctrl', 'cpm.fuentecasosrvc', 'cpm.equipoctrl', 'cpm.equiposrvc',
        'cpm.ubicacionctrl', 'cpm.ubicacionsrvc', 'cpm.tipollamadactrl', 'cpm.tipollamadasrvc', 'cpm.tiposolucionctrl',
        'cpm.tiposolucionsrvc', 'cpm.tecnicoctrl', 'cpm.tecnicosrvc', 'cpm.marcactrl', 'cpm.marcasrvc',
        'cpm.grupopartectrl', 'cpm.grupopartesrvc', 'cpm.partectrl', 'cpm.partesrvc', 'ngFileUpload',
        'cpm.estatuscasosrvc', 'cpm.rptintegranualctrl', 'cpm.shortenstrfltr', 'cpm.razoncambiosrvc', 'cpm.razoncambioctrl',
        'cpm.salidasrvc', 'cpm.bodegasrvc', 'cpm.bodegactrl', 'cpm.rptpdcasosfuentesrvc', 'cpm.rptpdcasosfuentectrl',
        'cpm.rptpdcasostecsctrl', 'cpm.rptpdcasostecssrvc', 'cpm.rptconttecfuentectrl', 'cpm.rptconttecfuentesrvc', 'cpm.rptcasoscerradosctrl',
        'cpm.rptintegraanualsrvc', 'cpm.calculaintegractrl'
    ]);

    app.config(['$routeProvider', 'fcsaNumberConfigProvider', function ($routeProvider, fcsaNumberConfigProvider) {
        $routeProvider.when('/', { templateUrl: 'pages/blank.html' });
        $routeProvider.when('/:name', { templateUrl: 'pages/blank.html', controller: 'PagesController' });
		
		// POS
        $routeProvider
        .when('/pos/:tipo/:pagina', { 
            templateUrl: 'pos/pages/base.html', 
            controller: 'posRutasController' 
        });
		
        $routeProvider.otherwise({redirectTo: '/'});
        fcsaNumberConfigProvider.setDefaultOptions({ preventInvalidInput: true });
    }]);

    app.run(['$rootScope', '$window', 'authSrvc', 'tipoCambioSrvc', function($rootScope, $window, authSrvc, tipoCambioSrvc){
        $rootScope.workingon = 0;
        $rootScope.logged = false;
        $rootScope.$on("$routeChangeStart", function (event, next, current) {
            tipoCambioSrvc.getTC();
            authSrvc.getSession().then(function(res){
                if(parseInt(res.uid) > 0){
                    $rootScope.logged = true;
                    $rootScope.uid = parseInt(res.uid);
                    $rootScope.fullname = res.nombre;
                    $rootScope.usuario = res.usuario;
                    $rootScope.correoe = res.correoe;
                    $rootScope.workingon = parseInt(res.workingon);
                }else{
                    var nextUrl = next.$$route.originalPath;
                    if(nextUrl == "/"){
                        if(parseInt(res.uid) == 0){
                            $window.location.href = 'index.html';
                        }
                    }else{
                        $window.location.href = 'index.html';
                    }
                }
            });
        });

    }]);

}());