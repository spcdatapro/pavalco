(function(){

    var rptlibventactrl = angular.module('cpm.rptlibventactrl', []);

    rptlibventactrl.controller('rptLibroVentasCtrl', ['$scope', 'rptLibroVentaSrvc', 'authSrvc', function($scope, rptLibroVentaSrvc, authSrvc){

        $scope.params = {mes: (moment().month() + 1).toString(), anio: moment().year(), idempresa: 0};
        $scope.libroventas = [];
        $scope.meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $scope.totiva = {activo: 0.0, bien: 0.0, servicio: 0.0};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                //authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        $scope.resetData = function(){
            $scope.libroventas = [];
            $scope.totiva = {activo: 0.0, bien: 0.0, servicio: 0.0};
        };

        function procDataLibVentas(d){
            var total = {
                fechafactura: '', tipodocumento:'', serie: '', documento: '', nit: '', cliente: 'TOTALES --->',
                exento: 0.0, activo: 0.0, bien: 0.0, servicio: 0.0, iva: 0.0, totfact: 0.0
            };
            for(var i = 0; i < d.length; i++){
                d[i].fechafactura = moment(d[i].fechafactura).toDate();
                //d[i].documento = parseInt(d[i].documento);
                d[i].iva = parseFloat(parseFloat(d[i].iva).toFixed(2));
                total.iva += d[i].iva;
                d[i].exento = parseFloat(parseFloat(d[i].exento).toFixed(2));
                total.exento += d[i].exento;
                d[i].activo = parseFloat(parseFloat(d[i].activo).toFixed(2));
                total.activo += d[i].activo;
                if(d[i].activo > 0) { $scope.totiva.activo += d[i].iva; }
                d[i].bien = parseFloat(parseFloat(d[i].bien).toFixed(2));
                total.bien += d[i].bien;
                if(d[i].bien > 0) { $scope.totiva.bien += d[i].iva; }
                d[i].servicio = parseFloat(parseFloat(d[i].servicio).toFixed(2));
                total.servicio += d[i].servicio;
                if(d[i].servicio > 0) { $scope.totiva.servicio += d[i].iva; }
                d[i].totfact = parseFloat(parseFloat(d[i].totfact).toFixed(2));
                total.totfact += d[i].totfact;
            }
            d.push(total);
            return d;
        }

        $scope.getLibVenta = function(){
            rptLibroVentaSrvc.rptLibroVentas($scope.params.idempresa, parseInt($scope.params.mes), $scope.params.anio).then(function(d){
                $scope.libroventas = procDataLibVentas(d);
            });
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Libro de ventas');
        };

    }]);

}());
