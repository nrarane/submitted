var request = require('request');

module.exports = {
    doGet
};
function doGet(url, formData){
    console.log('formdata: ', formData);

    request.get(url, {form: formData}, function(err, resp, body){
        console.log('response: ', body);
        return (JSON.parse(body));
    });
}