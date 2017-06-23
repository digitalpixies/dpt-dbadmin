angular
  .module('DPTDBAdminApp', [
    'ngAnimate',
    'ngCookies',
    'ngResource',
//    'ngRoute',
    'ngSanitize',
    'ngTouch',
    'ui.bootstrap',
//    'ngFileUpload'
  ]);

angular.module('DPTDBAdminApp')
  .factory('TablesAPI', function ($resource) {
    return $resource(dpt_dbadmin.rest_url+"dpt-dbadmin/v1/tables/:id",
      {id:'@id'},
      {
        query:{
          headers: {'X-WP-Nonce': dpt_dbadmin.nonce},
          isArray:true
        },
        get:{
          headers: {'X-WP-Nonce': dpt_dbadmin.nonce}
        }
      }
    );
  })
  .factory('QueryAPI', function ($resource) {
    return $resource(dpt_dbadmin.rest_url+"dpt-dbadmin/v1/queries/:id",
      {id:'@id'},
      {
        query:{
          headers: {'X-WP-Nonce': dpt_dbadmin.nonce},
          isArray:true
        },
        get:{
          headers: {'X-WP-Nonce': dpt_dbadmin.nonce}
        },
        save:{
          method:'POST',
          headers: {'X-WP-Nonce': dpt_dbadmin.nonce}
        }
      }
    );
  })
  .controller('CRUDCtrl', function ($scope, $http, $httpParamSerializerJQLike, $uibModal, $document, TablesAPI, QueryAPI) {
    $scope.tables=TablesAPI.query({});
    $scope.control={};
    $scope.control.max_pages=5;
    $scope.query={};
    $scope.tablename=null;
    $scope.query.columns={};
    $scope.query.control = {};
    $scope.query.control.ToggleSettings=function() {
      $scope.query.control._displaySettings=!$scope.query.control._displaySettings;
    };
    $scope.query.control.CloseSettings=function() {
      $scope.query.control._displaySettings=false;
      $scope.query.control.Execute();
    }
    $scope.query.page_size = 10;
    $scope.query.current_page = 1;
    $scope.query.count = 0;
    $scope.query.control.SelectTable = function(tablename) {
      TablesAPI.get({id:tablename}, function(result) {
        $scope.query.columns=result.columns;
        $scope.query.availableColumns=result.availableColumns;
        $scope.query.count=result.count;
        $scope.query.page_size=result.page_size;
        $scope.query.offset=result.offset;
        $scope.query.control.Execute();
      });
    };
    $scope.query.control.AddColumn = function(column) {
      $scope.query.columns.push(column);
    };
    $scope.query.control.RemoveColumn = function(index) {
      $scope.query.columns.splice(index,1);
    };
    $scope.query.control.AddAllColumns = function() {
      for(var i in $scope.query.availableColumns) {
        $scope.query.control.AddColumn($scope.query.availableColumns[i]);
      }
    };
    $scope.query.control.Execute = function() {
      var params = {
        tablename:$scope.query.tablename,
        columns:$scope.query.columns,
        availableColumns:$scope.query.availableColumns,
        page_size:$scope.query.page_size,
        offset:($scope.query.current_page-1)*$scope.query.page_size
      };
      var query = new QueryAPI(params);
      query.$save(function(data) {
        console.log(data);
        $scope.query.results=data.results;
      });
    }
//    console.log(dpt_dbadmin);
  });
