export const dnsServers = {
  nofilter: [
    {
      name: 'Google DNS',
      ip: '8.8.8.8',
      category: 'nofilter',
    },
    {
      name: 'Cloudflare',
      ip: '1.1.1.1',
      category: 'nofilter',
    },
  ],
  secure: [
    {
      name: 'Quad9',
      ip: '9.9.9.9',
      category: 'secure',
    },
    {
      name: 'CleanBrowsing',
      ip: '185.228.168.9',
      category: 'secure',
    },
  ],
  adblock: [
    {
      name: 'AdGuard',
      ip: '94.140.14.14',
      category: 'adblock',
    },
    {
      name: 'NextDNS',
      ip: '45.90.28.0',
      category: 'adblock',
    },
  ],
};

export const getAllServers = () => {
  return Object.values(dnsServers).flat();
};

export const getServersByCategory = (category) => {
  return dnsServers[category];
};

export default dnsServers;