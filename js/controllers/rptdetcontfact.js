(function(){

    var rptdetcontfactctrl = angular.module('cpm.rptdetcontfactctrl', []);

    rptdetcontfactctrl.controller('rptDetContFactCtrl', ['$scope', 'detContSrvc', 'authSrvc', 'proveedorSrvc', 'empresaSrvc', function($scope, detContSrvc, authSrvc, proveedorSrvc, empresaSrvc){

        $scope.objEmpresa = {};
        $scope.losProvs = [];
        $scope.params = { idempresa: 0, fDel: moment().startOf('month').toDate(), fAl: moment().endOf('month').toDate(), idprov: 0,
            serie: '', documento: '', fdelstr: '', falstr:'' };
        $scope.lasFact = [];
        $scope.objProv = [];
        $scope.data = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                    $scope.objEmpresa = d[0];
                    $scope.params.idempresa = parseInt($scope.objEmpresa.id);
                });
            }
        });

        proveedorSrvc.lstProveedores().then(function(d) {
            $scope.losProvs = d;
            $scope.losProvs.push({id:0, nitnombre: 'Todos los proveedores'});
        });

        $scope.getDetContFact = function(){
            $scope.params.idprov = $scope.objProv[0] !== null && $scope.objProv[0] !== undefined ? ($scope.objProv.length == 1 ? $scope.objProv[0].id : 0) : 0;
            $scope.params.serie = $scope.params.serie !== null && $scope.params.serie !== undefined ? $scope.params.serie : '';
            $scope.params.documento = $scope.params.documento !== null && $scope.params.documento !== undefined ? $scope.params.documento : 0;
            $scope.params.fdelstr = moment($scope.params.fDel).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.fAl).format('YYYY-MM-DD');
            detContSrvc.rptDetContFact($scope.params).then(function(d){
                $scope.lasFact = d;
                $scope.styleData();
            });
        };

        function indexOfProv(myArray, searchTerm) {
            var index = -1;
            for(var i = 0, len = myArray.length; i < len; i++) {
                if (myArray[i].idprov === searchTerm) {
                    index = i;
                    break;
                }
            }
            return index;
        };

        function indexOfCompra(myArray, searchTerm) {
            var index = -1;
            for(var i = 0, len = myArray.length; i < len; i++) {
                if (myArray[i].idcompra === searchTerm) {
                    index = i;
                    break;
                }
            }
            return index;
        };

        function getProvs(){
            var uniqueProvs = [];
            for(var x = 0; x < $scope.lasFact.length; x++){
                if(indexOfProv (uniqueProvs, parseInt($scope.lasFact[x].idproveedor)) < 0){
                    uniqueProvs.push({
                        idprov: parseInt($scope.lasFact[x].idproveedor),
                        nit: $scope.lasFact[x].nit,
                        nombre: $scope.lasFact[x].nombre
                    });
                };
            };
            return uniqueProvs;
        };

        function getCompras(){
            var uniqueCompras = [];
            for(var x = 0; x < $scope.lasFact.length; x++){
                //arrayObjectIndexOf(uniqueCompras, $scope.lasFact[x].idcompra, 'idcompra')
                if(indexOfCompra(uniqueCompras, parseInt($scope.lasFact[x].idcompra)) < 0){
                    uniqueCompras.push({
                        idprov: parseInt($scope.lasFact[x].idproveedor),
                        idcompra: parseInt($scope.lasFact[x].idcompra),
                        documento: $scope.lasFact[x].documento,
                        fechaingreso: $scope.lasFact[x].fechaingreso,
                        fechapago: $scope.lasFact[x].fechapago,
                        totfact: $scope.lasFact[x].totfact
                    });
                };
            };
            return uniqueCompras;
        };

        $scope.styleData = function(){
            $scope.data = [];
            var qProvs = getProvs();
            var qCompras = getCompras();
            var tmp = {};
            var sumas = {totDebe: 0.0, totHaber: 0.0};

            for(var i = 0; i < qProvs.length; i++){
                $scope.data.push({
                    idprov: qProvs[i].idprov,
                    nit: qProvs[i].nit,
                    nombre: qProvs[i].nombre,
                    facturas: []
                });
            };
            for(var i = 0; i < $scope.data.length; i++){
                for(var j = 0; j < qCompras.length; j++){
                    if(qCompras[j].idprov === $scope.data[i].idprov){
                        $scope.data[i].facturas.push({
                            idcompra: qCompras[j].idcompra,
                            documento: qCompras[j].documento,
                            fechaingreso: qCompras[j].fechaingreso,
                            fechapago: qCompras[j].fechapago,
                            totfact: parseFloat(qCompras[j].totfact),
                            detcont: []
                        });
                    };
                };
            };
            for(var i = 0; i < $scope.data.length; i++){
                for(var j = 0; j < $scope.data[i].facturas.length; j++){
                    sumas = {totDebe: 0.0, totHaber: 0.0};
                    for(var k = 0; k < $scope.lasFact.length; k++){
                        if(parseInt($scope.lasFact[k].idcompra) === $scope.data[i].facturas[j].idcompra){
                            $scope.data[i].facturas[j].detcont.push({
                                codigo: $scope.lasFact[k].codigo,
                                nombrecta: $scope.lasFact[k].nombrecta,
                                debe: parseFloat($scope.lasFact[k].debe),
                                haber: parseFloat($scope.lasFact[k].haber)
                            });
                            sumas.totDebe += parseFloat($scope.lasFact[k].debe);
                            sumas.totHaber += parseFloat($scope.lasFact[k].haber);
                        };
                    };
                    $scope.data[i].facturas[j].detcont.push({
                        codigo: 'Total',
                        nombrecta: '--->',
                        debe: sumas.totDebe,
                        haber: sumas.totHaber
                    });
                };
            };
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Detalle contable de facturas');
        };

    }]);

}());