import re
import urllib2
from Queue import Queue
import thread
import time
import traceback
import os
import time
import socket
import gzip
import cStringIO
import urlparse
import json
import cgi
import sys
import HTMLParser

g_max_worker = 10
socket.setdefaulttimeout(15)

g_cate_filter = {
0:True,

'SLEEP': False,
'ACCESSORIES': False,
'FLIP FLOPS': False,
'SWIM': False,
'YOGA': False,
'SHORTS': False,
'SKIRTS': False,
'UNDERWEAR': False,
'GEAR': False

}

g_prod_filter = lambda rp, sp: sp / rp < 0.3

g_links = (
(12202, 'http://www.abercrombie.com/shop/us/mens'),
(12203, 'http://www.abercrombie.com/shop/us/womens'),
(12551, 'http://www.hollisterco.com/shop/us/dudes'),
(12552, 'http://www.hollisterco.com/shop/us/bettys'),
)

#------------------------------

g_queue = Queue()
g_data = [None, ] * g_max_worker


def get_content(l, tn=10):
    d = None
    for i in range(tn):
        try:
            r = urllib2.Request(l)
            r.add_header('Accept-Encoding', 'gzip')
            f = urllib2.urlopen(r)
            if f.code == 200:
                if f.headers.get('Content-Encoding', '').lower() == 'gzip':
                    d= gzip.GzipFile(fileobj=cStringIO.StringIO(f.read())).read()
                else:
                    d = f.read()
                break
            print "[CD Error]", l, f.code
        except Exception, e:
            print "[GC Error]", l, e
            
    return d


regx_prod_blk = re.compile('<li class="product-wrap[^"]*">(.+?)</li>', re.S|re.M|re.I)
regx_prod_lst = re.compile('prod-img-([0-9]+)"[^>]+src="([^"]+)".+?href="([^"]+)"[^>]*>([^>]+)</a>.+?\$([0-9\.]+)</span>.+?\$([0-9\.]+)</span>', re.S|re.M|re.I)
def get_subcate(wi, job):
    (topcate_id, link), cate_name = job[1:]
    
    #param = cgi.parse_qs(link.split('?')[1])
    #cate_id = int(param['categoryId'][0])

    #if g_cate_filter.has_key(cate_id):
    #    if not g_cate_filter[cate_id]: return
    #else:
    cate_id = 0
    if True:
        nz = cate_name.upper()
        if g_cate_filter.has_key(nz):
            if not g_cate_filter[nz]: return
        elif not g_cate_filter[0]: return
    
    d = get_content(link)
    if not d: return

    pd = []
    for p in regx_prod_blk.findall(d):
        k = regx_prod_lst.search(p)
        if not k: continue
        k = k.groups()
        
        regular_price = float(k[4])
        sale_price = float(k[5])
        if not g_prod_filter(regular_price, sale_price): continue
        
        pid = int(k[0])
        pimg = urlparse.urljoin(link, k[1].replace('&amp;', '&'))
        purl = urlparse.urljoin(link, k[2].replace('&amp;', '&'))
        pname = k[3]
        
        pd.append( ( pid, pimg, purl, pname, '%0.2f'%(regular_price,), '%0.2f'%(sale_price,) ) )
    
    if pd:
        print "top:%u, cate:%u, name:%s, prod:%u" % (topcate_id, cate_id, cate_name, len(pd))
        g_data[wi].append( (topcate_id, cate_id, cate_name, pd) )


def get_clearance_ul(data):
    k = '>clearance</a>'
    idx = data.lower().find(k)
    if idx < 0: return None
    data = data[idx + len(k):].strip()
    if data[:3] != '<ul': return None
    
    sidx = 0
    while True:
        eidx = data.find('</ul>', sidx)
        if eidx < 0: break
        sidx = data.rfind('<ul', 0, eidx)
        if sidx == 0: return data[:eidx]
        else: data = data[:sidx] + data[eidx + 5:]
        
    return None
    
regx_maincate_lnk = re.compile('<a[^>]*? href="([^"]*)"[^>]*>Clearance</a>', re.S|re.M|re.I)
regx_cate_lst = re.compile('<a[^>]*? href="([^"]*)"[^>]*>([^<]*)</a>', re.S|re.M|re.I)
def get_maincate(i, job):
    topcate_id, link = job[1]
    
    d = get_content(link)
    if not d: return
    m = regx_maincate_lnk.search(d)
    if not m: return
    
    flink = urlparse.urljoin(link, m.group(1).replace('&amp;', '&'))
    d = get_content(flink)
    if not d: return
    
    m = get_clearance_ul(d)
    if not m: return
    m = regx_cate_lst.findall(m)
    for l in m:
        cate_link = urlparse.urljoin(flink, l[0].replace('&amp;', '&'))
        cate_name = l[1]
        g_queue.put( (1, (topcate_id, cate_link), cate_name) )


def do_work(i, job):
    if job[0]:
        get_subcate(i, job)
    else:
        get_maincate(i, job)

def worker(i):
    while True:
        job = g_queue.get()
        try:
            do_work(i, job)
        except Exception, e:
            print e
            traceback.print_exc()
        g_queue.task_done()


def main():
    for l in g_links: g_queue.put( (0, l, None) )
    
    for i in range(g_max_worker):
        g_data[i] = []
        thread.start_new_thread(worker, (i, ))
        #worker(i)
    g_queue.join()
    print "Done"
    
    
    data = []
    for d in g_data: data.extend(d)
    
    fw = open('data_tmp.txt', 'wb')
    json.dump(data, fw)
    fw.close()


print "++af.py", os.getpid(), sys.version

try:
    import fcntl
except:
    fcntl = None
    
try:
    fd = os.open("af.lock", os.O_CREAT | os.O_RDONLY)
    try:
        if fcntl != None: fcntl.flock(fd, fcntl.LOCK_EX|fcntl.LOCK_NB)
        
        try:
            print time.strftime("%x %X")
            main()
            print "load into database"
            sys.stdout.flush()
            os.system('/usr/bin/php-cli af.php 2>&1')
            print
            
        finally:
            if fcntl != None: fcntl.flock(fd, fcntl.LOCK_UN)
    finally:
        os.close(fd)
    
except Exception, e:
    print 'main error:', e
    print traceback.format_exc()

print "--af.py", os.getpid()


