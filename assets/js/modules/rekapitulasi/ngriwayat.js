app.controller('HistoryCtrl', function ($scope, $http, $timeout) {

  $scope.rows      = [];
  $scope.page      = 1;
  $scope.limit     = 20;
  $scope.hasMore   = true;
  $scope.filter    = {};
  $scope.images    = [];
  $scope.loadingImg = false;

  let debounce;

  /* =====================
     INIT
     ===================== */
  $scope.init = function () {
    $scope.load(true);
  };

  /* =====================
     LOAD LIST (TANPA GAMBAR)
     ===================== */
  $scope.load = function (reset = false) {

    if (reset) {
      $scope.page    = 1;
      $scope.rows    = [];
      $scope.hasMore = true;
    }

    $http.get(base_url + 'history/api', {
      params: {
        page:  $scope.page,
        limit:$scope.limit,
        ...$scope.filter
      }
    }).then(res => {

      if (res.data.length < $scope.limit) {
        $scope.hasMore = false;
      }

      $scope.rows = $scope.rows.concat(res.data);
      $scope.page++;

    });
  };

  /* =====================
     SEARCH (DEBOUNCE)
     ===================== */
  $scope.onSearch = function () {
    if (debounce) $timeout.cancel(debounce);
    debounce = $timeout(() => $scope.load(true), 400);
  };

  /* =====================
     LOAD IMAGE (ON DEMAND)
     ===================== */
  $scope.showImages = function (row) {

    if (row.images) {
      $scope.images = row.images;
      return;
    }

    $scope.loadingImg = true;

    $http.get(base_url + 'history/images/' + row.id)
      .then(res => {
        row.images     = res.data;
        $scope.images  = res.data;
      })
      .finally(() => {
        $scope.loadingImg = false;
      });
  };

});
