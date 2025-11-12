# Early Access Signup API - Backend Setup Guide

## Overview

This guide will help you set up an AWS Lambda function with API Gateway to handle early access signups. The implementation includes:

- API Gateway with rate limiting to prevent spam
- Lambda function to process email submissions
- S3 bucket to store emails in a text file
- Email notification service to send submissions to early-access@arguspam.com

## Architecture

```
User Form → API Gateway (Rate Limited) → Lambda Function → S3 (emails.txt)
                                              ↓
                                        Email Service (notification)
```

## Prerequisites

- AWS Account
- AWS CLI installed and configured
- Node.js installed locally (for testing)
- Email service API key (SendGrid, Mailgun, or SMTP credentials)

## Step 1: Create S3 Bucket

1. Go to AWS S3 Console
2. Click "Create bucket"
3. Bucket name: `arguspam-early-access` (must be globally unique)
4. Region: Choose your preferred region (e.g., `us-east-1`)
5. Keep default settings and create bucket
6. Create an empty file `emails.txt` and upload it to the bucket

**OR via AWS CLI:**

```bash
aws s3 mb s3://arguspam-early-access
echo "" > emails.txt
aws s3 cp emails.txt s3://arguspam-early-access/emails.txt
```

## Step 2: Create IAM Role for Lambda

1. Go to IAM Console → Roles
2. Click "Create role"
3. Select "AWS service" → "Lambda"
4. Add these permissions:
   - `AWSLambdaBasicExecutionRole` (for CloudWatch logs)
   - Create custom inline policy for S3:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:GetObject",
        "s3:PutObject"
      ],
      "Resource": "arn:aws:s3:::arguspam-early-access/*"
    }
  ]
}
```

5. Name the role: `arguspam-early-access-lambda-role`

## Step 3: Create Lambda Function

### 3.1 Create Function in AWS Console

1. Go to Lambda Console
2. Click "Create function"
3. Choose "Author from scratch"
4. Function name: `arguspam-early-access-signup`
5. Runtime: `Node.js 20.x`
6. Architecture: `x86_64`
7. Use existing role: `arguspam-early-access-lambda-role`
8. Click "Create function"

### 3.2 Lambda Function Code

Copy the following code into the Lambda function editor:

```javascript
import { S3Client, GetObjectCommand, PutObjectCommand } from "@aws-sdk/client-s3";
import https from 'https';

const s3Client = new S3Client({ region: process.env.AWS_REGION });
const BUCKET_NAME = process.env.BUCKET_NAME || 'arguspam-early-access';
const FILE_KEY = 'emails.txt';
const NOTIFICATION_EMAIL = 'early-access@arguspam.com';

// Email service configuration (choose one)
// Option 1: SendGrid
const SENDGRID_API_KEY = process.env.SENDGRID_API_KEY;

// Option 2: Mailgun
const MAILGUN_API_KEY = process.env.MAILGUN_API_KEY;
const MAILGUN_DOMAIN = process.env.MAILGUN_DOMAIN;

// Option 3: SMTP (e.g., Gmail)
const SMTP_HOST = process.env.SMTP_HOST;
const SMTP_PORT = process.env.SMTP_PORT;
const SMTP_USER = process.env.SMTP_USER;
const SMTP_PASS = process.env.SMTP_PASS;

// Helper function to read emails from S3
async function getExistingEmails() {
  try {
    const command = new GetObjectCommand({
      Bucket: BUCKET_NAME,
      Key: FILE_KEY
    });

    const response = await s3Client.send(command);
    const fileContent = await streamToString(response.Body);

    return fileContent
      .split('\n')
      .map(email => email.trim().toLowerCase())
      .filter(email => email.length > 0);
  } catch (error) {
    if (error.name === 'NoSuchKey') {
      return [];
    }
    throw error;
  }
}

// Helper function to convert stream to string
async function streamToString(stream) {
  const chunks = [];
  for await (const chunk of stream) {
    chunks.push(chunk);
  }
  return Buffer.concat(chunks).toString('utf-8');
}

// Helper function to save emails to S3
async function saveEmail(emails) {
  const content = emails.join('\n') + '\n';

  const command = new PutObjectCommand({
    Bucket: BUCKET_NAME,
    Key: FILE_KEY,
    Body: content,
    ContentType: 'text/plain'
  });

  await s3Client.send(command);
}

// Helper function to send email notification
async function sendEmailNotification(email) {
  const emailService = process.env.EMAIL_SERVICE || 'sendgrid';

  try {
    if (emailService === 'sendgrid') {
      await sendViaSendGrid(email);
    } else if (emailService === 'mailgun') {
      await sendViaMailgun(email);
    } else if (emailService === 'smtp') {
      await sendViaSMTP(email);
    }
  } catch (error) {
    console.error('Failed to send email notification:', error);
    // Don't throw error - we still want to return 200 to user
  }
}

// SendGrid implementation
async function sendViaSendGrid(email) {
  const data = JSON.stringify({
    personalizations: [{
      to: [{ email: NOTIFICATION_EMAIL }]
    }],
    from: { email: 'noreply@arguspam.com' },
    subject: 'New Early Access Signup',
    content: [{
      type: 'text/plain',
      value: `New early access signup: ${email}`
    }]
  });

  return new Promise((resolve, reject) => {
    const options = {
      hostname: 'api.sendgrid.com',
      path: '/v3/mail/send',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${SENDGRID_API_KEY}`,
        'Content-Type': 'application/json',
        'Content-Length': data.length
      }
    };

    const req = https.request(options, (res) => {
      if (res.statusCode >= 200 && res.statusCode < 300) {
        resolve();
      } else {
        reject(new Error(`SendGrid returned status ${res.statusCode}`));
      }
    });

    req.on('error', reject);
    req.write(data);
    req.end();
  });
}

// Mailgun implementation
async function sendViaMailgun(email) {
  const postData = new URLSearchParams({
    from: 'Argus PAM <noreply@arguspam.com>',
    to: NOTIFICATION_EMAIL,
    subject: 'New Early Access Signup',
    text: `New early access signup: ${email}`
  }).toString();

  const auth = Buffer.from(`api:${MAILGUN_API_KEY}`).toString('base64');

  return new Promise((resolve, reject) => {
    const options = {
      hostname: 'api.mailgun.net',
      path: `/v3/${MAILGUN_DOMAIN}/messages`,
      method: 'POST',
      headers: {
        'Authorization': `Basic ${auth}`,
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': postData.length
      }
    };

    const req = https.request(options, (res) => {
      if (res.statusCode >= 200 && res.statusCode < 300) {
        resolve();
      } else {
        reject(new Error(`Mailgun returned status ${res.statusCode}`));
      }
    });

    req.on('error', reject);
    req.write(postData);
    req.end();
  });
}

// SMTP implementation (using nodemailer would require a Lambda Layer)
// For simplicity, using basic implementation
async function sendViaSMTP(email) {
  // Note: For production, consider using a Lambda Layer with nodemailer
  // This is a simplified version
  console.log(`Would send email via SMTP to ${NOTIFICATION_EMAIL} for ${email}`);
  // Implementation would require nodemailer library in a Lambda Layer
}

// Validate email format
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

export const handler = async (event) => {
  console.log('Received event:', JSON.stringify(event));

  try {
    // Parse request body
    let body;
    try {
      body = JSON.parse(event.body || '{}');
    } catch (error) {
      console.error('JSON parse error:', error);
      return {
        statusCode: 400,
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Headers': 'Content-Type',
          'Access-Control-Allow-Methods': 'POST, OPTIONS'
        },
        body: JSON.stringify({ error: 'Invalid request' })
      };
    }

    console.log('Parsed body:', JSON.stringify(body));

    const email = body.email?.trim().toLowerCase();
    console.log('Email extracted:', email);

    // Validate email
    if (!email || !isValidEmail(email)) {
      console.log('Email validation failed. Email is empty or invalid format.');
      return {
        statusCode: 400,
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Headers': 'Content-Type',
          'Access-Control-Allow-Methods': 'POST, OPTIONS'
        },
        body: JSON.stringify({ error: 'Invalid email address' })
      };
    }

    // Get existing emails
    const existingEmails = await getExistingEmails();

    // Check if email already exists
    if (!existingEmails.includes(email)) {
      // Add new email
      existingEmails.push(email);
      await saveEmail(existingEmails);

      // Send notification email
      await sendEmailNotification(email);
    }

    // Always return 200 whether email was new or already existed
    return {
      statusCode: 200,
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type',
        'Access-Control-Allow-Methods': 'POST, OPTIONS'
      },
      body: JSON.stringify({ message: 'Success' })
    };

  } catch (error) {
    console.error('Error processing request:', error);

    // Return 400 for any other errors
    return {
      statusCode: 400,
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type',
        'Access-Control-Allow-Methods': 'POST, OPTIONS'
      },
      body: JSON.stringify({ error: 'An error occurred' })
    };
  }
};
```

### 3.3 Configure Environment Variables

In the Lambda Console, go to Configuration → Environment variables and add:

**Required:**
- `BUCKET_NAME`: `arguspam-early-access`
- `EMAIL_SERVICE`: `sendgrid` or `mailgun` or `smtp`

**For SendGrid:**
- `SENDGRID_API_KEY`: Your SendGrid API key

**For Mailgun:**
- `MAILGUN_API_KEY`: Your Mailgun API key
- `MAILGUN_DOMAIN`: Your Mailgun domain (e.g., `mg.arguspam.com`)

**For SMTP:**
- `SMTP_HOST`: SMTP server hostname
- `SMTP_PORT`: SMTP port (usually 587)
- `SMTP_USER`: SMTP username
- `SMTP_PASS`: SMTP password

### 3.4 Configure Lambda Settings

1. Go to Configuration → General configuration
2. Increase timeout to 30 seconds
3. Memory: 256 MB should be sufficient

## Step 4: Create API Gateway

### 4.1 Create REST API

1. Go to API Gateway Console
2. Click "Create API"
3. Choose "REST API" (not private)
4. Click "Build"
5. Choose "New API"
6. API name: `arguspam-early-access-api`
7. Click "Create API"

### 4.2 Create Resource and Method

1. Click "Create Resource"
2. Resource path: `/signup`
3. Enable CORS: Check this box
4. Click "Create Resource"

5. Select the `/signup` resource
6. Click "Create Method"
7. Method type: `POST`
8. Integration type: `Lambda Function`
9. Enable Lambda Proxy Integration: Check this box
10. Lambda Function: Select `arguspam-early-access-signup`
11. Click "Create Method"

### 4.3 Enable CORS (if not already enabled)

1. Select the `/signup` resource
2. Click "Enable CORS"
3. Keep default settings
4. Click "Save"

### 4.4 Configure Rate Limiting (Usage Plan)

1. In API Gateway Console, go to "Usage Plans" (left sidebar)
2. Click "Create"
3. Name: `early-access-rate-limit`
4. Enable throttling:
   - Rate: `10` requests per second
   - Burst: `20` requests
5. Enable quota (optional):
   - `1000` requests per day per API key
6. Click "Next"

7. Click "Add API Stage"
8. Select your API and stage (create deployment first - see Step 4.5)
9. Click "Add" then "Done"

### 4.5 Deploy API

1. Click "Deploy API"
2. Deployment stage: Create new stage
3. Stage name: `prod`
4. Click "Deploy"

5. Note your API endpoint URL (e.g., `https://abc123.execute-api.us-east-1.amazonaws.com/prod`)

### 4.6 Configure Additional Rate Limiting (WAF - Optional but Recommended)

For IP-based rate limiting to prevent spam:

1. Go to AWS WAF Console
2. Click "Create web ACL"
3. Name: `arguspam-early-access-waf`
4. Resource type: `Amazon API Gateway`
5. Select your API Gateway
6. Add rules:
   - Rule type: `Rate-based rule`
   - Name: `rate-limit-signup`
   - Rate limit: `100` requests per 5 minutes per IP
   - Action: `Block`
7. Create web ACL

**Cost note:** AWS WAF has a cost ($5/month + $1/rule + $0.60 per million requests). For a simple solution, you can skip WAF and rely on API Gateway throttling.

## Step 5: Email Service Setup

Choose one of the following options:

### Option A: SendGrid (Recommended - Free tier: 100 emails/day)

1. Sign up at https://sendgrid.com
2. Verify your email
3. Create an API key:
   - Settings → API Keys → Create API Key
   - Full Access or Mail Send access
   - Copy the API key
4. Add sender email:
   - Settings → Sender Authentication
   - Verify `noreply@arguspam.com` or your domain
5. Add API key to Lambda environment variables

### Option B: Mailgun (Free tier: 5,000 emails/month for 3 months)

1. Sign up at https://www.mailgun.com
2. Add and verify your domain
3. Get API key from Settings → API Keys
4. Add API key and domain to Lambda environment variables

### Option C: Gmail SMTP (Free but limited)

1. Create a Gmail account or use existing
2. Enable 2-factor authentication
3. Create App Password:
   - Google Account → Security → 2-Step Verification → App passwords
4. Use SMTP settings:
   - Host: `smtp.gmail.com`
   - Port: `587`
   - User: Your Gmail address
   - Pass: App password

Note: Gmail has sending limits (500 emails/day)

## Step 6: Testing

### 6.1 Test with cURL

```bash
curl -X POST https://YOUR-API-ID.execute-api.REGION.amazonaws.com/prod/signup \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'
```

Expected response:
```json
{
  "message": "Success"
}
```

### 6.2 Check S3 Bucket

```bash
aws s3 cp s3://arguspam-early-access/emails.txt -
```

You should see the email added to the file.

### 6.3 Test Rate Limiting

Run the curl command rapidly multiple times. After exceeding the rate limit, you should receive a 429 (Too Many Requests) response.

## Step 7: Update Frontend Configuration

After deploying your API, you need to update the frontend code with your API endpoint:

1. Open the file: `landing/coming-soon/v2/src/js/utils/early-access-form.js`

2. Update the `API_ENDPOINT` constant at the top of the file:

```javascript
const API_ENDPOINT = 'https://YOUR-API-ID.execute-api.REGION.amazonaws.com/prod/signup';
```

Replace:
- `YOUR-API-ID` with your actual API Gateway ID
- `REGION` with your AWS region (e.g., `us-east-1`)

Example:
```javascript
const API_ENDPOINT = 'https://abc123def.execute-api.us-east-1.amazonaws.com/prod/signup';
```

3. Build and deploy your frontend (if applicable)

The form will now submit emails to your Lambda function!

## Step 8: Monitor and Maintain

### CloudWatch Logs

- Lambda logs: CloudWatch → Log groups → `/aws/lambda/arguspam-early-access-signup`
- API Gateway logs: Enable in API Gateway → Stages → Logs/Tracing

### Download Emails

To download all emails for processing:

```bash
aws s3 cp s3://arguspam-early-access/emails.txt ./emails.txt
```

### Clear Emails (if needed)

```bash
echo "" > emails.txt
aws s3 cp emails.txt s3://arguspam-early-access/emails.txt
```

## Troubleshooting

### Common Issues

1. **CORS errors**: Ensure CORS is enabled on API Gateway and OPTIONS method is configured
2. **Permission denied on S3**: Check IAM role has correct S3 permissions
3. **Email not sending**: Check CloudWatch logs for email service errors
4. **Rate limiting too strict**: Adjust throttling settings in Usage Plan or WAF

### Debugging

1. Check CloudWatch Logs for Lambda errors
2. Enable API Gateway logging: Stages → Logs/Tracing → CloudWatch Settings
3. Test Lambda directly in console with test event

**IMPORTANT**: When testing Lambda in the AWS Console, you must use the API Gateway proxy event format. The email must be in `event.body` as a **JSON string**, not as a direct object.

**Correct Test Event** (copy this exactly):

```json
{
  "body": "{\"email\":\"test@example.com\"}",
  "headers": {
    "Content-Type": "application/json"
  },
  "httpMethod": "POST",
  "isBase64Encoded": false,
  "path": "/signup"
}
```

**Common Mistake** - This will NOT work (body is an object, not a string):
```json
{
  "body": {
    "email": "test@example.com"
  }
}
```

**How to test in Lambda Console:**

1. Go to your Lambda function
2. Click the "Test" tab
3. Create a new test event with the correct format above
4. Click "Test"
5. Check the "Execution result" and "CloudWatch logs" for debugging info

The logs will now show:
- `Received event:` - The full event structure
- `Parsed body:` - The parsed JSON body
- `Email extracted:` - The email value extracted

If you see `Email extracted: undefined`, your test event body format is incorrect.

## Cost Estimation

- Lambda: Free tier 1M requests/month, then $0.20 per 1M requests
- API Gateway: Free tier 1M requests/month, then $3.50 per 1M requests
- S3: Minimal (<$0.01/month for a small text file)
- Email service: Free tier (SendGrid: 100/day, Mailgun: 5,000/month)
- WAF (optional): ~$5/month + $1/rule

**Total estimated cost**: $0-1/month for low traffic (within free tiers)

## Security Best Practices

1. Enable CloudTrail for API auditing
2. Regularly review CloudWatch logs
3. Use AWS Secrets Manager for sensitive credentials (instead of environment variables)
4. Enable API Gateway request validation
5. Consider adding honeypot fields or CAPTCHA for additional spam protection

## Next Steps

1. Update frontend to integrate with this API
2. Set up monitoring alerts (CloudWatch Alarms)
3. Configure custom domain for API Gateway
4. Add analytics tracking
