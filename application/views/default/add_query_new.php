<!DOCTYPE html>
<html lang="en">
<head>
  <title>Db Query Form</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body>

<div class="container">

  <div class="panel panel-primary">
	  <div class="panel-heading"> <h3 class="panel-title">Run your query here</h3> </div>
	  <div class="panel-body">

		  <form class="form-horizontal" method="post" action="">
		  <div class="form-group">
			<label for="Query" class="col-sm-2 control-label">Query</label>
			<div class="col-sm-10">
			  <textarea class="form-control" id="Query" placeholder="Paste your query here" name="query"></textarea>
			</div>
		  </div>
		  <div class="form-group">
			<label for="password" class="col-sm-2 control-label">Password</label>
			<div class="col-sm-10">
			  <input type="password" class="form-control" id="password" placeholder="Password" name="password">
			</div>
		  </div>

		  <div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
			  <input type="submit" class="btn btn-default" value="Execute">
			</div>
		  </div>
		  </form>
	  </div>
  </div>

</div>

</body>
</html>