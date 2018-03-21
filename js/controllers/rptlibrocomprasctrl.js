(function(){

    var rptlibcompctrl = angular.module('cpm.rptlibcompctrl', []);

    rptlibcompctrl.controller('rptLibroComprasCtrl', ['$scope', 'rptLibroComprasSrvc', 'authSrvc', function($scope, rptLibroComprasSrvc, authSrvc){

        $scope.params = {mes: (moment().month() + 1).toString(), anio: moment().year(), idempresa: 0};
        $scope.librocompras = [];
        $scope.meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $scope.totiva = {combustible: 0.0, bien: 0.0, servicio: 0.0, importaciones: 0.0};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                //authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        $scope.resetData = function(){
            $scope.librocompras = [];
            $scope.totiva = {combustible: 0.0, bien: 0.0, servicio: 0.0, importaciones: 0.0};
        };

        function procDataLibComp(d){
            var total = {
                fechafactura: '', tipodocumento:'', serie: '', documento: '', nit: '', proveedor: 'TOTALES --->',
                combustible: 0.0, bien: 0.0, servicio: 0.0, bienex: 0.0, servicioex: 0.0, importaciones: 0.0, iva: 0.0, totfact: 0.0
            };
            for(var i = 0; i < d.length; i++){
                d[i].fechafactura = moment(d[i].fechafactura).toDate();
                d[i].documento = parseInt(d[i].documento);
                d[i].iva = parseFloat(parseFloat(d[i].iva).toFixed(2));
                total.iva += d[i].iva;
                d[i].combustible = parseFloat(parseFloat(d[i].combustible).toFixed(2));
                total.combustible += d[i].combustible;
                if(d[i].combustible > 0) { $scope.totiva.combustible += d[i].iva; }
                d[i].bien = parseFloat(parseFloat(d[i].bien).toFixed(2));
                total.bien += d[i].bien;
                if(d[i].bien > 0) { $scope.totiva.bien += d[i].iva; }
                d[i].servicio = parseFloat(parseFloat(d[i].servicio).toFixed(2));
                total.servicio += d[i].servicio;
                if(d[i].servicio > 0) { $scope.totiva.servicio += d[i].iva; }
                d[i].bienex = parseFloat(parseFloat(d[i].bienex).toFixed(2));
                total.bienex += d[i].bienex;
                d[i].servicioex = parseFloat(parseFloat(d[i].servicioex).toFixed(2));
                total.servicioex += d[i].servicioex;
                d[i].importaciones = parseFloat(parseFloat(d[i].importaciones).toFixed(2));
                total.importaciones += d[i].importaciones;
                if(d[i].importaciones > 0) { $scope.totiva.importaciones += d[i].iva; }
                d[i].totfact = parseFloat(parseFloat(d[i].totfact).toFixed(2));
                total.totfact += d[i].totfact;
            }
            d.push(total);
            return d;
        }

        $scope.getLibComp = function(){
            rptLibroComprasSrvc.rptLibroCompras($scope.params.idempresa, parseInt($scope.params.mes), $scope.params.anio).then(function(d){
                $scope.librocompras = procDataLibComp(d);
            });
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Libro de compras');
        };

    }]);

}());
