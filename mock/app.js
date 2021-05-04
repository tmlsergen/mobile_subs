let express = require('express');
const moment = require('moment')

const app = express();

app.use(express.json()) // for parsing application/json
app.use(express.urlencoded({extended: true})) // for parsing application/x-www-form-urlencoded

app.get('/', function (request, response) {
    response.send('Purchase Mock');
});

app.post('/ios/purchase-ios', function (req, resp) {
    const receipt = req.body.receipt;
    const client = req.body.client;

    let lastLetter = receipt.slice(-1)

    let now = moment().format('YYYY-MM-DD hh:mm:ss');

    if (lastLetter % 2 == 0) {
        resp.send({
            status: false,
            client: client
        })
    }

    resp.send({
        status: true,
        expire_date: now,
        client: client
    });
})

app.post('/google/purchase-google', function (req, resp) {
    const receipt = req.body.receipt;
    const client = req.body.client;

    let lastLetter = receipt.slice(-1)

    let now = moment().format('YYYY-MM-DD hh:mm:ss');


    if (lastLetter % 2 == 0) {
        resp.send({
            status: false,
            client: client
        })
    }

    resp.send({
        status: true,
        expire_date: now,
        client: client
    });
});

app.post('/google/detect-google', function (req, resp) {
    const subscription = req.body.subscription;

    let lastLetter = subscription.receipt.slice(-2)

    let now = moment().format('YYYY-MM-DD hh:mm:ss');


    if (lastLetter % 6 == 0) {
        resp.status(400).send({
            error: 'rate-limit'
        })
    }

    resp.send({
        status: 'success',
        id: subscription.id
    });
});

app.post('/google/detect-ios', function (req, resp) {
    const subscription = req.body.subscription;

    let lastLetter = subscription.receipt.slice(-2)

    let now = moment().format('YYYY-MM-DD hh:mm:ss');


    if (lastLetter % 6 == 0) {
        resp.status(400).send({
            error: 'rate-limit'
        })
    }

    resp.send({
        status: 'success',
        id: subscription.id
    });
});

app.listen(3000, function () {
    console.log('mock api started');
});