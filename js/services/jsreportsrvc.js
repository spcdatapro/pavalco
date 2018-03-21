(function(){

    var jsreportsrvc = angular.module('cpm.jsreportsrvc', []);

    jsreportsrvc.factory('jsReportSrvc', ['$http', function($http){
        var url = window.location.origin + ':5489/api/report';
        //var url = 'http://52.35.3.1:5489/api/report';
        var props = {}, test = false;
        return {
            integracionAnualXLSX: function(obj){
                props = {'template':{'shortid': test ? 'Hy4g7vklZ' : 'HkwNgY1lZ'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            promDiarioCasosFuente: function(obj){
                props = {'template':{'shortid': test ? 'rkKwCjlW-' : 'r1NMbQ8--'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            promDiarioCasosTecnicos: function(obj){
                props = {'template':{'shortid': test ? 'rJ7eTRbWb' : 'HkNYlm8Zb'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            conteoFuenteTecnico: function(obj){
                props = {'template':{'shortid': test ? 'Syq48QMZb' : 'Syq01mU-Z'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            casosCerrados: function(obj){
                props = {'template':{'shortid': test ? 'Hk5xUf8ZW' : 'BJgVJ7U-b'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            balanceDeSaldos: function(obj){
                props = {'template':{'shortid': test ? 'ryEq3BlT' : 'Bk3FpQLT'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            balanceDeSaldosXlsx: function(obj){
                props = {'template':{'shortid': test ? 'HyJCHNX0' : 'SyKnddVR'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            balanceGeneral: function(obj){
                props = {'template':{'shortid': test ? 'SJOrlu4a' : 'Hy4F3XLp'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            balanceGeneralXlsx: function(obj){
                props = {'template':{'shortid': test ? 'rJ1ybymC' : 'r1tLOu4C'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            estadoResultados: function(obj){
                props = {'template':{'shortid': test ? 'SJn-zxSp' : 'B1FQAXUa'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            estadoResultadosXlsx: function(obj){
                props = {'template':{'shortid': test ? 'HkZkilVR' : 'ByFeYOER'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            libroMayor: function(obj){
                props = {'template':{'shortid': test ? 'SJz5mtHp' : 'S1urgEIT'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            libroMayorXlsx: function(obj){
                props = {'template':{'shortid': test ? 'B1yIO-4A' : 'HJy9KdV0'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            libroDiario: function(obj){
                props = {'template':{'shortid': test ? 'rkQOf3HT' : 'HyG7yEU6'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            libroDiarioXlsx: function(obj){
                props = {'template':{'shortid': test ? 'BJRBVSNA' : 'SyRStuEA'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            conciliacionBancaria: function(obj){
                if(parseInt(obj.resumido) == 1){
                    props = {'template':{'shortid': test ? 'BJNpt9-pe' : ''}, 'data': obj};
                }else{
                    props = {'template':{'shortid': test ? 'BJNpt9-pe' : ''}, 'data': obj};
                }
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            conciliacionBancariaXlsx: function(obj){
                if(parseInt(obj.resumido) == 1){
                    props = {'template':{'shortid': test ? 'ryLgZSCll' : 'SJNiPH0ll'}, 'data': obj};
                }else{
                    props = {'template':{'shortid': test ? 'rkcsEEHgg' : 'SJtsErSee'}, 'data': obj};
                }
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            saldoClientes: function(obj){
                props = {'template':{'shortid': test ? 'HkF-ravex' : 'rkFrpqM-l'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            saldoClientesXlsx: function(obj){
                props = {'template':{'shortid': test ? 'SyNszxYex' : 'HkXopcMWx'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            saldoProveedores: function(obj){
                props = {'template':{'shortid': test ? 'BJ6TDltge' : 'rk10pcz-e'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            saldoProveedoresXlsx: function(obj){
                props = {'template':{'shortid': test ? 'HJkEjgKxl' : 'BJdMA5G-g'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiClientes: function(obj){
                props = {'template':{'shortid': test ? 'rkVpRgtex' : 'BJfgRwfWe'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiClientesXlsx: function(obj){
                props = {'template':{'shortid': test ? 'SyBzUWFxx' : 'r1rjfHfbl'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiClientesDet: function(obj){
                props = {'template':{'shortid': test ? 'SJf2ZEkZx' : 'SkPEy_MZx'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiClientesDetXlsx: function(obj){
                props = {'template':{'shortid': test ? 'rkwu2UkZx' : 'Bybtg_Gbe'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiProveedores: function(obj){
                props = {'template':{'shortid': test ? 'H1jsPgWZg' : 'rkJKbOGbe'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiProveedoresXlsx: function(obj){
                props = {'template':{'shortid': test ? 'SJSvfoG-g' : 'SyRbL2fbl'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiProveedoresDet: function(obj){
                props = {'template':{'shortid': test ? 'r1Z2kW-Wg' : 'SkDij_fbx'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            antiProveedoresDetXlsx: function(obj){
                props = {'template':{'shortid': test ? 'B10PQjG-e' : 'HyROSnMWl'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            ecuentaProveedores: function(obj){
                props = {'template':{'shortid': test ? 'HJRqDnb-e' : 'HyjHpOGWg'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            ecuentaProveedoresXlsx: function(obj){
                props = {'template':{'shortid': test ? '' : 'BkEpHkcfl'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            ecuentaClientes: function(obj){
                props = {'template':{'shortid': test ? 'SJaAdNzbx' : 'r1junOfZl'}, 'data': obj};

                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            ecuentaClientesXlsx: function(obj){
                props = {'template':{'shortid': test ? 'B1dUNiGWx' : 'ryNUAAKzg'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            },
            integracionClientes: function(obj){
                props = {'template':{'shortid': test ? '' : 'HyzaHUHvg'}, 'data': obj};
                return $http.post(url, props, {responseType: 'arraybuffer'}).success(function(response){return response});
            }
        };
    }]);

}());
