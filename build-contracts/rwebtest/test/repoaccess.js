// smoke test, wait for dependencies etc
const http = require('http');
const expect = require('chai').expect;
const mixIn = require('mout/object/mixIn');

const testid = 'test' + Date.UTC();

function preq(requestOptions) {
  var options = mixIn({
    hostname: 'rweb',
    port: 80,
    method: 'GET'
  }, requestOptions);
  return new Promise(function(resolve, reject) {
    return http.request(options, (res) => {
      console.log('Request OK');
      //res.setEncoding('utf8');
      resolve(res /* gives you: Converting circular structure to JSON */);
    }).on('error', (e) => {
      console.log('Request failed');
      reject(e);
    }).end();
  });
};

describe("http://rweb", function() {

  describe("/admin/repocreate", function() {

    var r;
    before(function(done) {
      r = preq({
        path: '/admin/repocreate'
      }).then(done).catch(function(e) {
        console.warn('promise-request failed', e.message);
        throw e;
      });
    });

    it ("Responds 200 when a repository is created", function() {
      expect(r).to.have.property('then').that.is.a('function');
      return r.then(function(res) {
        expect(res).to.have.property('status').that.equals(200);
      });
    });

  });

});
