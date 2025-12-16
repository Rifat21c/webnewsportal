# Deployment Guide

To put your website online instantly, we use SSH Tunneling. This exposes your local server (running on port 3000) to the public internet.

## Command

Run this command in your terminal:

```powershell
ssh -o StrictHostKeyChecking=no -R 80:localhost:3000 serveo.net
```

## How it works
1. **ssh**: The tool we use to connect.
2. **-R 80:localhost:3000**: Tells the remote server (serveo.net) to forward traffic from its port 80 to your local port 3000.
3. **serveo.net**: The free service providing the tunnel.

## Notes
- Keep the terminal window **OPEN**. If you close it, the site goes offline.
- The URL will be shown in the output (e.g., `https://random-name.serveo.net`).
- If serveo.net is down, try `ssh -R 80:localhost:3000 nokey@localhost.run`.
