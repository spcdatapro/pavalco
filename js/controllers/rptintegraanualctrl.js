(function(){

    var rptintegranualctrl = angular.module('cpm.rptintegranualctrl', []);

    rptintegranualctrl.controller('rptIntegracionAnualCtrl', ['$scope', 'jsReportSrvc', 'rptIntegracionAnualSrvc', 'casoSrvc', '$uibModal', function($scope, jsReportSrvc, rptIntegracionAnualSrvc, casoSrvc, $uibModal){
        $scope.meses = [
            {id:  1, 'mes': 'Enero'}, {id:  2, 'mes': 'Febrero'}, {id:  3, 'mes': 'Marzo'}, {id:  4, 'mes': 'Abril'}, {id:  5, 'mes': 'Mayo'}, {id:  6, 'mes': 'Junio'},
            {id:  7, 'mes': 'Julio'}, {id:  8, 'mes': 'Agosto'}, {id:  9, 'mes': 'Septiembre'}, {id: 10, 'mes': 'Octubre'}, {id: 11, 'mes': 'Noviembre'}, {id: 12, 'mes': 'Diciembre'}
        ];

        $scope.nommes = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $scope.ver = [false, false, false, false, false, false, false, false, false, false, false, false];

        $scope.params = {anio: moment().year(), dmes: (moment().startOf('year').month() + 1).toString(), ames: (moment().month() + 1).toString(), tt: 1};
        $scope.integracion = null;
        $scope.casos = [];

        $scope.getRepHtml = function(){
            $scope.ver = [false, false, false, false, false, false, false, false, false, false, false, false];
            rptIntegracionAnualSrvc.rptIntegracionAnual($scope.params).then(function(d){
                $scope.integracion = d;
                for(var i = +$scope.params.dmes; i <= +$scope.params.ames; i++){ $scope.ver[i - 1] = true; }
                //console.log($scope.integracion);
            });
        };
		
		function setData(d){
            for(var i = 0; i < d.length; i++){
                d[i].fhapertura = moment(d[i].fhapertura).toDate();
                d[i].fhcierre = moment(d[i].fhcierre).toDate();
            }
            return d;
        }

        $scope.detcelda = function(dmes, ames, anio, tipo, idtipo){
            var filtros = {
                fdelstr: anio + '-' + (+dmes < 10 ? '0' + dmes : dmes) + '-' + '01 00:00:00',
                falstr: (moment(anio + '-' + (+ames < 10 ? '0' + ames : ames) + '-' + '01').endOf('month').format('YYYY-MM-DD')) + ' 23:59:59',
                idtipollamada: +tipo === 1 ? idtipo : '',
                idtipocaso: +tipo === 2 ? idtipo : '',
                idubicacion: +tipo === 3 ? idtipo : '',
                idtiposolucion: '', idtecnico: '', idfuentecaso: ''
            };
            //console.log(filtros); return;
            casoSrvc.lstCasosCerrados(filtros).then(function(d){
                $scope.casos = setData(d);
                //console.log($scope.casos);
                var modalInstance = $uibModal.open({
                    animation: true,
                    templateUrl: 'modalDetCasos.html',
                    controller: 'ModalDetCasosCtrl',
                    windowClass: 'app-modal-window',
                    resolve:{
                        casos: function(){ return $scope.casos; },
                        filtros: function(){ return filtros; }
                    }
                });

                modalInstance.result.then(function(){ return 0; }, function(){ return 0; });
            });
        };

        $scope.getReporte = function(){
            jsReportSrvc.integracionAnualXLSX($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                var nombre = $scope.params.anio + '_' + $scope.nommes[+$scope.params.dmes - 1] + '_' + $scope.nommes[+$scope.params.ames - 1];
                saveAs(file, 'IntegracionAnual_' + nombre + '.xlsx');
            });
        };

    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//

    rptintegranualctrl.controller('ModalDetCasosCtrl', ['$scope', '$uibModalInstance', 'casos', 'filtros', 'jsReportSrvc', function($scope, $uibModalInstance, casos, filtros, jsReportSrvc){
        $scope.cerrados = casos;

        $scope.ok = function () { $uibModalInstance.close(); };

        $scope.cancel = function () { $uibModalInstance.dismiss('cancel'); };

        $scope.getRepDetalle= function(){
            var test = false;
            jsReportSrvc.getReport(test ? 'H1bK37eaz' : 'S1wJW4laG', filtros).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.ms-excel'});
                var nombre = 'DetalleCasosCerrados';
                saveAs(file, nombre + '.xlsx');
            });
        };

    }]);

}());
