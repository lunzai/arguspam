// console.log('=== ENVIRONMENT DEBUG ===');
// console.log('ORIGIN env var:', process.env.ORIGIN);
// console.log('NODE_ENV:', process.env.NODE_ENV);
// console.log('All env vars starting with ORIGIN:', Object.keys(process.env).filter(key => key.includes('ORIGIN')));
// console.log('=== END ENVIRONMENT DEBUG ===');

export async function handle({ event, resolve }) {
//   // Trust proxy headers and reconstruct the original HTTPS URL
//   const forwardedProto = event.request.headers.get('x-forwarded-proto');
//   const forwardedHost = event.request.headers.get('x-forwarded-host') || event.request.headers.get('host');
  
//   if (forwardedProto && forwardedHost && event.url.protocol !== `${forwardedProto}:`) {
//     // Reconstruct the URL with the correct protocol
//     const correctUrl = `${forwardedProto}://${forwardedHost}${event.url.pathname}${event.url.search}`;
    
//     // Replace the URL object with the corrected one
//     Object.defineProperty(event, 'url', {
//       value: new URL(correctUrl),
//       writable: false,
//       enumerable: true,
//       configurable: true
//     });
    
//     console.log('=== URL CORRECTED ===');
//     console.log('Original URL:', event.request.url);
//     console.log('Corrected URL:', event.url.href);
//     console.log('=== END CORRECTION ===');
//   }
  
  return resolve(event);
}