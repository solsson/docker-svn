// smoke test, wait for dependencies etc
const http = require('http');
const expect = require('chai').expect;
const retry = require('retry');

describe("rweb", function() {

  it("Responds", function(done) {

    // TODO maybe switch to http://caolan.github.io/async/docs.html#retry
    var operation = retry.operation({
    });

    operation.attempt(function(currentAttempt) {
      console.log('Trying....');
      var req = http.get({
        hostname: 'rweb',
        port: 80,
        path: '/'
      }, (res) => {
        expect(res.statusCode).to.equal(200);
        res.setEncoding('utf8');
        res.on('data', function(chunk) {
          console.log('Rweb server response at root:', chunk);
          done();
        });
      });
      req.on('error', (e) => {
        console.log('Retrying...');
        if (operation.retry(e)) {
          throw new Error('Retried, but failed: ' + e);
        }
      });
    });

  });

});
