(function(){

    var rptcasoscerradosctrl = angular.module('cpm.rptcasoscerradosctrl', []);

    rptcasoscerradosctrl.controller('rptCasosCerradosCtrl', ['$scope', 'jsReportSrvc', '$sce', 'casoSrvc', '$window', 'tecnicoSrvc', 'fuenteCasoSrvc', 'tipoSolucionSrvc', 'ubicacionSrvc', 'tipoLlamadaSrvc', function($scope, jsReportSrvc, $sce, casoSrvc, $window, tecnicoSrvc, fuenteCasoSrvc, tipoSolucionSrvc, ubicacionSrvc, tipoLlamadaSrvc){

        $scope.params = {
            del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), lostecs: undefined, lostiposc: undefined, lostipossol: undefined, lasubica: undefined,
            lostiposll: undefined, lasfuentes: undefined
        };
        $scope.cerrados = [];
        $scope.tecnicos = [];
        $scope.tiposcaso = [];
        $scope.tipossolucion = [];
        $scope.ubicaciones = [];
        $scope.tiposllamada = [];
        $scope.fuentes = [];

        tecnicoSrvc.lstTecnicos().then(function(d){ $scope.tecnicos = d; });
        fuenteCasoSrvc.lstAllTiposCaso().then(function(d){ $scope.tiposcaso = d; });
        tipoSolucionSrvc.lstTiposSolucion().then(function(d){ $scope.tipossolucion = d; });
        ubicacionSrvc.lstUbicaciones().then(function(d){ $scope.ubicaciones = d; });
        tipoLlamadaSrvc.lstTiposLlamada().then(function(d){ $scope.tiposllamada = d; });
        fuenteCasoSrvc.lstFuentesCaso().then(function(d){ $scope.fuentes = d; });

        function listIds(obj){
            var lst = '';
            if(obj != null && obj != undefined){
                if(obj.length > 0){
                    for(var i = 0; i < obj.length; i++){
                        if(lst != ''){lst += ','}
                        lst += obj[i].id;
                    }
                    return lst;
                }else{ return lst; }
            }else{ return lst; }
        }

        function setParams(){
            $scope.params.fdelstr = (moment($scope.params.del).format('YYYY-MM-DD') + ' 00:00:00');
            $scope.params.falstr = (moment($scope.params.al).format('YYYY-MM-DD') + ' 23:59:59');
            $scope.params.idtecnico = listIds($scope.params.lostecs);
            $scope.params.idtipocaso = listIds($scope.params.lostiposc);
            $scope.params.idtiposolucion = listIds($scope.params.lostipossol);
            $scope.params.idubicacion = listIds($scope.params.lasubica);
            $scope.params.idtipollamada = listIds($scope.params.lostiposll);
            $scope.params.idfuentecaso = listIds($scope.params.lasfuentes);
        }

        function setData(d){
            for(var i = 0; i < d.length; i++){
                d[i].fhapertura = moment(d[i].fhapertura).toDate();
                d[i].fhcierre = moment(d[i].fhcierre).toDate();
            }
            return d;
        }

        $scope.getRepHtml = function(){
            setParams();
            casoSrvc.lstCasosCerrados($scope.params).then(function(d){
                $scope.cerrados = setData(d);
            });
        };

        $scope.getRepPdf = function(){
            setParams();
            jsReportSrvc.casosCerrados($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $window.open($sce.trustAsResourceUrl(fileURL));
            });
        };

    }]);
}());