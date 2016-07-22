import thread

class Queue:
    
    def __init__(self):
        self._data = []
        self._lock = thread.allocate_lock()
        self._waiting_list = []
        self._task = 0
        self._joining_list = []
    
    def qsize(self):
        self._lock.acquire()
        n = len(self._data)
        self._lock.release()
    
    def put(self, item):
        self._lock.acquire()
        self._data.append(item)
        if self._waiting_list:
            for l in self._waiting_list: l.release()
            self._waiting_list = []
        self._lock.release()
    
    def get(self):
        item = None
        self._lock.acquire()
        l = thread.allocate_lock()
        while True:
            if len(self._data) == 0:
                l.acquire()
                self._waiting_list.append(l)
                self._lock.release()
                l.acquire()
                l.release()
                self._lock.acquire()
            else:
                item = self._data.pop()
                self._task += 1
                break
        self._lock.release()
        return item
    
    def task_done(self):
        self._lock.acquire()
        self._task -= 1
        if len(self._data) == 0 and self._task == 0:
            if self._joining_list:
                for l in self._joining_list: l.release()
                self._joining_list = []
        self._lock.release()
    
    def join(self):
        self._lock.acquire()
        l = thread.allocate_lock()
        while True:
            if len(self._data) > 0 or self._task > 0:
                l.acquire()
                self._joining_list.append(l)
                self._lock.release()
                l.acquire()
                l.release()
                self._lock.acquire()
            else:
                break
        self._lock.release()
    
    